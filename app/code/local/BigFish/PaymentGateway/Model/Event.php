<?php
/**
* BIG FISH Ltd.
* http://www.bigfish.hu
*
* @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
* @category   BigFish
* @package    BigFish_PaymentGateway
* @author     Gabor Huszak / BIG FISH Ltd. -> huszy [at] bigfish [dot] hu
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
* @copyright  Copyright (c) 2011, BIG FISH Ltd.
*/
class BigFish_PaymentGateway_Model_Event
{
    const PAYMENTGATEWAY_STATUS_SUCCESS = "SUCCESSFUL";
    const PAYMENTGATEWAY_STATUS_CANCEL = "CANCELED";
    const PAYMENTGATEWAY_STATUS_PENDING = "PENDING";
    const PAYMENTGATEWAY_STATUS_ERROR = "ERROR";
    const PAYMENTGATEWAY_STATUS_TIMEOUT = "TIMEOUT";

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * Event request data
     * @var array
     */
    protected $_eventData = array();

    /**
     * Enent request data setter
     * @param array $data
     * @return BigFish_PaymentGateway_Model_Event
     */
    public function setEventData(array $data)
    {
        $this->_eventData = $data;
        return $this;
    }

    /**
     * Event request data getter
     * @param string $key
     * @return array|string
     */
    public function getEventData($key = null)
    {
        if (null === $key) {
            return $this->_eventData;
        }
        return isset($this->_eventData[$key]) ? $this->_eventData[$key] : null;
    }

    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Process status notification from PaymentGateway
     *
     * @return String
     */
    public function processStatusEvent()
    {
        try {
			$response = array();
			
			if (is_array($this->_eventData) && count($this->_eventData)) {
				$response[] = $this->_eventData['ResultMessage'].'<br />';
				
				if (strlen($this->_eventData['ProviderTransactionId'])) {
					$response[] = Mage::helper('paymentgateway')->__('Provider Transaction ID').': '.$this->_eventData['ProviderTransactionId'];
				}
				
				if (strlen($this->_eventData['Anum'])) {
					$response[] = Mage::helper('paymentgateway')->__('Anum').': '.$this->_eventData['Anum'];
				}
			}
			
            $params = $this->_validateEventData(false);
            $msg = '';
            switch($params['ResultCode']) {
                case self::PAYMENTGATEWAY_STATUS_TIMEOUT: //timeout
					$msg = (count($response) ? implode('<br />', $response) : Mage::helper('paymentgateway')->__('status_paymentTimeout'));
                    $this->_processCancel($msg);
                    break;				
                case self::PAYMENTGATEWAY_STATUS_ERROR: //Error
                    $msg = (count($response) ? implode('<br />', $response) : Mage::helper('paymentgateway')->__('status_paymentFailed'));
                    $this->_processCancel($msg);
                    break;
                case self::PAYMENTGATEWAY_STATUS_CANCEL: //cancel
					$msg = (count($response) ? implode('<br />', $response) : Mage::helper('paymentgateway')->__('status_paymentCancelled'));
                    $this->_processCancel($msg);
                    break;
                case self::PAYMENTGATEWAY_STATUS_PENDING: //pending
                    $msg = Mage::helper('paymentgateway')->__('status_paymentPending');
                    $this->_processSale($params['ResultCode'], $msg);
                    break;
                case self::PAYMENTGATEWAY_STATUS_SUCCESS: //ok
					$msg = (count($response) ? implode('<br />', $response) : Mage::helper('paymentgateway')->__('status_paymentSuccess'));
                    $this->_processSale($params['ResultCode'], $msg);
                    break;
            }
            return $msg;
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            Mage::logException($e);
        }
        return $msg;
    }

    /**
     * Process cancelation
     */
    public function cancelEvent() {
        try {
            $this->_validateEventData(false);
            $this->_processCancel(Mage::helper('paymentgateway')->__('status_paymentCancelled'));
            return Mage::helper('paymentgateway')->__('event_orderCancelled');
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            Mage::logException($e);
        }
        return '';
    }

    /**
     * Validate request and return QuoteId
     * Can throw Mage_Core_Exception and Exception
     *
     * @return int
     */
    public function successEvent(){
        $this->_validateEventData(false);
        return $this->_order->getQuoteId();
    }

    /**
     * Processed order cancelation
     * @param string $msg Order history message
     */
    protected function _processCancel($msg)
    {
        $this->_setTransactionStatus(BigFish_PaymentGateway_Helper_Data::TRANSACTION_STATUS_CANCELLED);
        $this->_addTransactionLog($msg."\nRESPONSE:\n".print_r($this->_eventData, true));
        $this->_order->cancel();
        $this->_order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, $msg);
        $this->_order->save();
    }

    /**
     * Processes payment confirmation, creates invoice if necessary, updates order status,
     * sends order confirmation to customer
     * @param string $msg Order history message
     */
    protected function _processSale($status, $msg)
    {
        switch ($status) {
            case self::PAYMENTGATEWAY_STATUS_SUCCESS:

                $this->_setTransactionStatus(BigFish_PaymentGateway_Helper_Data::TRANSACTION_STATUS_SUCCESS);
                $this->_addTransactionLog($msg."\nRESPONSE:\n".print_r($this->_eventData, true));

                $this->_createInvoice();
                $this->_order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $msg);
                // save transaction ID
                $this->_order->getPayment()->setLastTransId($this->getEventData('ProviderTransactionId'));
                // send new order email
                $this->_order->sendNewOrderEmail();
                $this->_order->setEmailSent(true);
                break;
            case self::PAYMENTGATEWAY_STATUS_PENDING:

                $this->_addTransactionLog($msg."\nRESPONSE:\n".print_r($this->_eventData, true));

                $this->_order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, $msg);
                // save transaction ID
                $this->_order->getPayment()->setLastTransId($this->getEventData('ProviderTransactionId'));
                break;
        }
        $this->_order->save();
    }

    /**
     * Builds invoice for order
     */
    protected function _createInvoice()
    {
        if (!$this->_order->canInvoice()) {
            return;
        }
        $invoice = $this->_order->prepareInvoice();
        $invoice->register()->capture();
        $this->_order->addRelatedObject($invoice);
    }

    /**
     * Checking returned parameters
     * Thorws Mage_Core_Exception if error
     * @param bool $fullCheck Whether to make additional validations such as payment status
     *
     * @return array  $params request params
     */
    protected function _validateEventData($fullCheck = true)
    {
        // get request variables
        $params = $this->_eventData;
        if (empty($params)) {
            Mage::throwException('Request does not contain any elements.');
        }

        // check Transaction ID
        if (empty($params['TransactionId'])) {
            Mage::throwException('Missing or invalid order ID.');
        }

        $collection=Mage::getModel("paymentgateway/paymentGateway")
                           ->getCollection()
                           ->addFieldToSelect('*')
                           ->addFieldToFilter('transaction_id',array('eq'=>$params["TransactionId"]))
                           ->addOrder('created_time','desc')
                           ->load();

        if($collection->getSize()==0) {
            Mage::throwException('Invalid Transaction Id');
        }

        $item = $collection->fetchItem();

        // load order for further validation
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($item->getOrderId());
        if (!$this->_order->getId()) {
            Mage::throwException('Order not found.');
        }

        if (0 !== strpos($this->_order->getPayment()->getMethodInstance()->getCode(), 'paymentgateway_')) {
            Mage::throwException('Unknown payment method.');
        }

        if($fullCheck) {
            if($item->getStatus()!=BigFish_PaymentGateway_Helper_Data::TRANSACTION_STATUS_STARTED)
            {
                Mage::throwException('Invalid transaction state.');
            }
        }

        return $params;
    }

    protected function _setTransactionStatus($status)
    {
        $collection=Mage::getModel("paymentgateway/paymentGateway")
                           ->getCollection()
                           ->addFieldToSelect('*')
                           ->addFieldToFilter('transaction_id',array('eq'=>$this->getEventData("TransactionId")))
                           ->load();
        $item = $collection->fetchItem();
        $item->setStatus($status)
             ->save();
    }

    protected function _addTransactionLog($debug)
    {
        $collection=Mage::getModel("paymentgateway/paymentGateway")
                           ->getCollection()
                           ->addFieldToSelect('*')
                           ->addFieldToFilter('transaction_id',array('eq'=>$this->getEventData("TransactionId")))
                           ->load();
        $item = $collection->fetchItem();
        $status = $item->getStatus();
        $id = $item->getId();

        $pgwLog = Mage::getModel('paymentgateway/log');
        $pgwLog->setPaymentgatewayId($id)
               ->setStatus($status)
               ->setCreatedTime(date("Y-m-d H:i:s"))
               ->setDebug($debug)
               ->save();
    }

}
