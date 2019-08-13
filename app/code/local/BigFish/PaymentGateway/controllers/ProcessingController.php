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

require_once(realpath(dirname(__FILE__)) . '/../PaymentGateway/Autoload.php');
BigFish\PaymentGateway\Autoload::register();

class BigFish_PaymentGateway_ProcessingController extends Mage_Core_Controller_Front_Action
{
    /* @var BigFish_PaymentGateway_Helper_Data */
    protected $_helper;

    protected function _construct()
	{
        parent::_construct();
        $this->_helper = Mage::helper('paymentgateway');
		$config = new BigFish\PaymentGateway\Config();
		$config->storeName = $this->_helper->getStoreName();
		$config->apiKey = $this->_helper->getApiKey();
		$config->testMode = $this->_helper->getIsTestMode();
		$config->outCharset = 'UTF-8';

		BigFish\PaymentGateway::setConfig($config);
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
     * set _paymentGateway
     *
	 * @param string $where
     */
    protected function _setPaymentGateway($where)
    {
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "SELECT 
				bpl.debug FROM bigfish_paymentgateway bp 
				LEFT JOIN bigfish_paymentgateway_log bpl ON bp.paymentgateway_id=bpl.paymentgateway_id 
				WHERE 
				bpl.status=".BigFish_PaymentGateway_Helper_Data::TRANSACTION_STATUS_INITED.
				$where." 
				ORDER BY 
				bpl.log_id asc 
				LIMIT 1";
		$row = $connection->fetchRow($sql);

		if (!(is_array($row) && !empty($row))) {
			Mage::throwException($this->_helper->__('process_noOrderFound'));
		}

		if ($row['debug'] == 'OTPSZEP') {
			$storeName = Mage::getStoreConfig('paymentgateway/paymentgateway_otpszep/storenameotpszep');
			$apiKey = Mage::getStoreConfig('paymentgateway/paymentgateway_otpszep/apikeyotpszep');

			$config = new BigFish\PaymentGateway\Config();
			$config->storeName = $storeName;
			$config->apiKey = $apiKey;
			$config->testMode = $this->_helper->getIsTestMode();
			$config->outCharset = 'UTF-8';

			BigFish\PaymentGateway::setConfig($config);
		}
    }	

    /**
     * Show orderPlaceRedirect page which contains the Moneybookers iframe.
     */
    public function paymentAction()
    {
        try {
            $checkout = $this->_getCheckout();

            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($checkout->getLastRealOrderId());
            if (!$order->getId()) {
                Mage::throwException($this->_helper->__('process_noOrderFound'));
            }

			$where = " AND bp.order_id='".$checkout->getLastRealOrderId()."'";
			$this->_setPaymentGateway($where);
			
            $collection=Mage::getModel("paymentgateway/paymentGateway")
                           ->getCollection()
                           ->addFieldToSelect('*')
                           ->addFieldToFilter('order_id',array('eq'=>$checkout->getLastRealOrderId()))
                           ->addFieldToFilter('status',array('eq'=>  BigFish_PaymentGateway_Helper_Data::TRANSACTION_STATUS_INITED))
                           ->addOrder('created_time','desc')
                           ->load();

            if($collection->getSize()==0) {
                Mage::throwException($this->_helper->__('process_noOrderFound'));
            }

            $item = $collection->fetchItem();
            $transactionId = $item->getTransactionId();

            $item->setStatus(BigFish_PaymentGateway_Helper_Data::TRANSACTION_STATUS_STARTED);
            $item->save();

            $log = Mage::getModel("paymentgateway/log");
            $log->setPaymentgatewayId($item->getId())
                ->setCreatedTime(date("Y-m-d H:i:s"))
                ->setStatus(BigFish_PaymentGateway_Helper_Data::TRANSACTION_STATUS_STARTED)
                ->setDebug("The customer was redirected to PaymentGateway.")
                ->save();

            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                $this->_helper->__('process_messageCustomerRedirected')
            );
            $order->save();

            $checkout->setPaymentGatewayQuoteId($checkout->getQuoteId());
            $checkout->setPaymentGatewayOrderId($checkout->getLastRealOrderId());
            $checkout->getQuote()->setIsActive(false)->save();
            $checkout->clear();

			$url = \BigFish\PaymentGateway::getStartUrl(new BigFish\PaymentGateway\Request\Start($transactionId));
			$this->_redirectUrl($url);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_error($e->getMessage());
        }
    }

    public function responseAction()
    {
        try {

            $urlParams = $this->getRequest()->getParams();
            if(!array_key_exists("TransactionId", $urlParams))
            {
                Mage::throwException($this->_helper->__('process_noTransactionIdInResponse'));
            }
            $transactionId = $urlParams["TransactionId"];
			
			$where = " AND bp.transaction_id='".$transactionId."'";
			$this->_setPaymentGateway($where);

			$response = BigFish\PaymentGateway::result(new BigFish\PaymentGateway\Request\Result($transactionId));

            Mage::log("PaymentGW Response: ".print_r($response,true));

			if (is_object($response)) {
				foreach (get_object_vars($response) as $response_key => $response_val) {
					$responseArray[$response_key] = $response_val;
				}
			} else {
				$responseArray = $response;
			}

            $event = Mage::getModel('paymentgateway/event')->setEventData($responseArray);
            $message = $event->processStatusEvent();

            $session = $this->_getCheckout();
            $session->setLastBigfishPaymentGatewayResult($responseArray);

            // Ha van session akkor irány vagy a cart vagy a success egyébként csak echo
            if($session->getPaymentGatewayOrderId()==$responseArray["OrderId"]) {
				$forceSuccess = false;

				$details = \BigFish\PaymentGateway::details(new BigFish\PaymentGateway\Request\Details($transactionId));
				
                if($forceSuccess || $responseArray["ResultCode"]==BigFish_PaymentGateway_Model_Event::PAYMENTGATEWAY_STATUS_SUCCESS) {
                    $quoteId = $event->successEvent();
                    $session->setLastSuccessQuoteId($quoteId);
					$session->addSuccess($message.'<br />'.$this->_helper->__('Paid amount').': '.$details->ProviderSpecificData->Amount.' '.$details->ProviderSpecificData->Currency);
                    $this->_redirect('checkout/onepage/success');
                }
				
                if(!$forceSuccess && 
					($responseArray["ResultCode"]==BigFish_PaymentGateway_Model_Event::PAYMENTGATEWAY_STATUS_ERROR ||
					$responseArray["ResultCode"]==BigFish_PaymentGateway_Model_Event::PAYMENTGATEWAY_STATUS_CANCEL ||
					$responseArray["ResultCode"]==BigFish_PaymentGateway_Model_Event::PAYMENTGATEWAY_STATUS_TIMEOUT ||
					$responseArray["ResultCode"]==BigFish_PaymentGateway_Model_Event::PAYMENTGATEWAY_STATUS_PENDING))
                {

                    if($quoteId = $session->getPaymentGatewayQuoteId()) {
                        $quote = Mage::getModel('sales/quote')->load($quoteId);
                        if ($quote->getId()) {
                            $quote->setIsActive(true)->save();
                            $session->setQuoteId($quoteId);
                        }
                    }

                    $session->addError($message);
                    $this->_redirect('checkout/cart');
                }

            } else {
                $this->getResponse()->setBody($message);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_error($e->getMessage());
        }
    }

    protected function _error($msg, $code = null)
    {
        $this->_getCheckout()->addError($msg);
        Mage::logException(new Mage_Core_Exception($msg, $code));
        $this->_redirect('checkout/cart');
    }

}
