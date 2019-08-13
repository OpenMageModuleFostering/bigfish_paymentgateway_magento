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

class BigFish_PaymentGateway_Model_Abstract extends Mage_Payment_Model_Method_Abstract
{
	const VERSION = '1.11.0';

    protected $_code  = 'paymentgateway_abstract';
    protected $_formBlockType = 'paymentgateway/form_redirect';
    protected $_infoBlockType = 'paymentgateway/info';

    /**
     * Availability options
     */
    protected $_isGateway              = true;
    protected $_canAuthorize           = true;
    protected $_canCapture             = true;
    protected $_canCapturePartial      = false;
    protected $_canRefund              = false;
    protected $_canVoid                = false;
    protected $_canUseInternal         = false;
    protected $_canUseCheckout         = true;
    protected $_canUseForMultishipping = false;

    protected $_paymentMethod    = 'abstract';
    protected $_defaultLocale    = 'hu';
    protected $_supportedLocales = array('en', 'hu', 'de');

    protected $_order;
    protected $_isInitSuccesfull = false;

    /**
     * Get order model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = $this->getInfoInstance()->getOrder();
        }
        return $this->_order;
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
     * Return url for redirection after order placed
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('paymentgateway/processing/payment');
    }

    /**
     * Capture payment
     *
     * @param Varien_Object $payment
     * @param decimal $amount
     * @return BigFish_PaymentGateway_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        Mage::log("Payment Capture");
        $payment->setStatus(self::STATUS_APPROVED)
            ->setTransactionId($this->getTransactionId())
            ->setIsTransactionClosed(0);

        return $this;
    }

    /**
     * Cancel payment
     *
     * @param Varien_Object $payment
     * @return BigFish_PaymentGateway_Model_Abstract
     */
    public function cancel(Varien_Object $payment)
    {
        Mage::log("Payment cancelled");
        $payment->setStatus(self::STATUS_DECLINED)
            ->setTransactionId($this->getTransactionId())
            ->setIsTransactionClosed(1);

        return $this;
    }

    
    /**
     * Return url of payment method
     *
     * @return string
     */
    public function getLocale()
    {
        $locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
        if (is_array($locale) && !empty($locale) && in_array($locale[0], $this->_supportedLocales)) {
            return $locale[0];
        }
        return $this->getDefaultLocale();
    }

    /**
     * prepare params array to send it to gateway page via POST
     *
     * @return array
     */
    public function getPaymentParams()
    {
        $order_id = $this->getOrder()->getRealOrderId();
		$extra = array();

		switch ($this->_paymentMethod) {
			case 'Borgun':
				$this->setItemsToExtra($extra);
				break;
			case 'OTPSimple':
			case 'OTPSimpleWire':
				$extra = array(
					'BILL_EMAIL' => $this->getOrder()->getCustomerEmail(),
				);
				$this->setItemsToExtra($extra);
				break;
		}
		
        $params = array(
            'provider'      => $this->_paymentMethod,
            'responseUrl'   => Mage::getUrl('paymentgateway/processing/response'),
            'amount'        => round($this->getOrder()->getGrandTotal(), 2),
            'orderId'       => $order_id,
            'userId'        => $this->getOrder()->getCustomerId(),
            'currency'      => $this->getOrder()->getOrderCurrencyCode(),
            'language'      => $this->getLocale(),
            'mppPhoneNumber'=> '',
            'OtpCardNumber' => '',
            'OtpExpiration' => '',
            'OtpCvc'        => '',
            'publicKey'		=> '',
            'MkbSzepCafeteriaId'  => '',
            'MkbSzepCardNumber'  => '',
            'MkbSzepCardCvv'  => '',
            'OtpCardPocketId'  => '',
			'OneClickPayment' => '',
            'AutoCommit'    => true,
			'extra'			=> $extra,
        );

        return $params;
    }
	
    /**
     * Set items to extra
     *
	 * @param array $extra
     * @return void
     */
    public function setItemsToExtra(&$extra)
    {
		$extra['productItems'] = array();

		$items = $this->getOrder()->getAllVisibleItems();

		if (is_array($items) && count($items)) {
			foreach ($items as $item) {
				$tax = $item->getTaxAmount();
				
				$extra['productItems'][] = array(
					'Name' => $item->getName(),
					'Description' => '',
					'Quantity' => $item->getQtyOrdered(),
					'Price' => round(($item->getPrice()+(((float)$tax > 0) ? (float)$tax : 0)), 2),
					'SKU' => $item->getSku(),
				);
			}

			$helper = Mage::helper('sales');
			
			if ((float)$this->getOrder()->getShippingAmount() > 0) {
				$extra['productItems'][] = array(
					'Name' => $helper->__('Shipping & Handling').' ('.$this->getOrder()->getShippingDescription().')',
					'Description' => '',
					'Quantity' => 1,
					'Price' => round($this->getOrder()->getShippingAmount(), 2),
					'SKU' => 'shipping',
				);
			}
			
			if ((float)$this->getOrder()->getDiscountAmount()) {
				$extra['productItems'][] = array(
					'Name' => $helper->__('Discount').' ('.$this->getOrder()->getDiscountDescription().')',
					'Description' => '',
					'Quantity' => 1,
					'Price' => round($this->getOrder()->getDiscountAmount(), 2),
					'SKU' => 'discount',
				);
			}
		}		
	}	

    /**
     * Get initialized flag status
     * @return true
     */
    public function isInitializeNeeded()
    {
        return true;
    }

	/**
	 * Instantiate state and set it to state onject
	 * @param $paymentAction
	 * @param $stateObject
	 */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);

        // PaymentGateway Init
        $helper = Mage::helper('paymentgateway');
        $paymentParams = $this->getPaymentParams();

		if ($paymentParams['provider'] == 'OTPSZEP') {
			$storeName = Mage::getStoreConfig('paymentgateway/paymentgateway_otpszep/storenameotpszep');
			$apiKey = Mage::getStoreConfig('paymentgateway/paymentgateway_otpszep/apikeyotpszep');
		} else {
			$storeName = $helper->getStoreName();
			$apiKey = $helper->getApiKey();
		}

		$config = new BigFish\PaymentGateway\Config();
		$config->storeName = $storeName;
		$config->apiKey = $apiKey;
		$config->testMode = $helper->getIsTestMode();
		$config->outCharset = 'UTF-8';

		BigFish\PaymentGateway::setConfig($config);

		$request = new BigFish\PaymentGateway\Request\Init();
        $request->setProviderName(($paymentParams['provider'] == 'OTPSZEP') ? BigFish\PaymentGateway::PROVIDER_OTP : $paymentParams['provider'])
                ->setResponseUrl($paymentParams['responseUrl'])
                ->setAmount($paymentParams['amount'])
                ->setCurrency($paymentParams['currency'])
                ->setOrderId($paymentParams['orderId'])
                ->setUserId($paymentParams['userId'])
                ->setLanguage($paymentParams['language'])
                ->setMppPhoneNumber($paymentParams['mppPhoneNumber'])
                ->setOtpCardNumber($paymentParams['OtpCardNumber'])
                ->setOtpExpiration($paymentParams['OtpExpiration'])
                ->setOtpCvc($paymentParams['OtpCvc'])
				->setOneClickPayment($paymentParams['OneClickPayment'])
				->setModuleName('Magento ('.Mage::getVersion().')')
				->setModuleVersion(self::VERSION);
		
		if ($paymentParams['provider'] == 'OTPSZEP') {
			$request->setOtpCardPocketId($paymentParams['OtpCardPocketId']);
		}
		
		if ($paymentParams['provider'] == BigFish\PaymentGateway::PROVIDER_OTP_TWO_PARTY) {
			$paymentParams['OtpCardNumber'] = '****************';
			$paymentParams['OtpExpiration'] = '****';
			$paymentParams['OtpCvc'] = '***';
		}
		
		if ($paymentParams['provider'] == BigFish\PaymentGateway::PROVIDER_MKB_SZEP) {
			$request->setMkbSzepCafeteriaId($paymentParams['MkbSzepCafeteriaId'])
				->setGatewayPaymentPage(true);
		}

		if ($paymentParams['provider'] === BigFish\PaymentGateway::PROVIDER_KHB_SZEP) {
			$extra['KhbCardPocketId'] = $paymentParams[BigFish\PaymentGateway::PROVIDER_KHB_SZEP]['KhbCardPocketId'];
		}
		
		/**
		 * Set Extra
		 * 
		 */
		if (isset($paymentParams['extra']) && is_array($paymentParams['extra']) && !empty($paymentParams['extra'])) {
			$request->setExtra($paymentParams['extra']);
		}

        $response = BigFish\PaymentGateway::init($request);

        $debugMsg = "PAYMENT_PARAMS:\n".print_r($paymentParams,true);
        $debugMsg.= "\n\nRESPONSE:\n".print_r($response,true);
        $pgwModel = Mage::getModel('paymentgateway/paymentGateway');
        $pgwLog = Mage::getModel('paymentgateway/log');
        $order = $this->getOrder();

        if ($response->ResultCode == BigFish\PaymentGateway::RESULT_CODE_SUCCESS && $response->TransactionId) {
            $pgwModel->setOrderId($order->getRealOrderId())
                     ->setTransactionId($response->TransactionId)
                     ->setCreatedTime(date("Y-m-d H:i:s"))
                     ->setStatus(BigFish_PaymentGateway_Helper_Data::TRANSACTION_STATUS_INITED)
                     ->save();

            $pgwLog->setPaymentgatewayId($pgwModel->getId())
                   ->setCreatedTime(date("Y-m-d H:i:s"))
                   ->setStatus(BigFish_PaymentGateway_Helper_Data::TRANSACTION_STATUS_INITED)
                   ->setDebug(trim($paymentParams['provider']))
                   ->save();
			
			$pgwLog2 = Mage::getModel('paymentgateway/log');
			
            $pgwLog2->setPaymentgatewayId($pgwModel->getId())
                   ->setCreatedTime(date("Y-m-d H:i:s"))
                   ->setStatus(BigFish_PaymentGateway_Helper_Data::TRANSACTION_STATUS_INITED)
                   ->setDebug($debugMsg)
                   ->save();
        } else {
            $paymentgatewayErrorMessage = "PAYMENT_PARAMS:\n".print_r($paymentParams, true)."\n\n";
            $paymentgatewayErrorMessage.= $response->ResultCode.": ".$response->ResultMessage;
            $paymentgatewayErrorMessage.= "<br/><br/><xmp>".print_r($response, true)."</xmp>";

            Mage::log($paymentgatewayErrorMessage);
            Mage::throwException("PaymentGateway Error: ".$response->ResultMessage);
        }
    }
    /**
     * Get config action to process initialization
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        $paymentAction = $this->getConfigData('payment_action');
        return empty($paymentAction) ? true : $paymentAction;
    }

}