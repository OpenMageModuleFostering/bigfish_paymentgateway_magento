<?php
/**
* BIG FISH Ltd.
* http://www.bigfish.hu
*
* @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
* @category   BigFish
* @package    BigFish_PaymentGateway
* @author     Tibor Nagy / BIG FISH Ltd. -> tibor [dot] nagy [at] bigfish [dot] hu
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
* @copyright  Copyright (c) 2016, BIG FISH Ltd.
*/
class BigFish_PaymentGateway_Model_Otpsimple extends BigFish_PaymentGateway_Model_Abstract
{
    protected $_formBlockType = 'paymentgateway/form_otpsimple';
    protected $_code  = 'paymentgateway_otpsimple';
    protected $_paymentMethod = 'OTPSimple';

	/**
	 * Prepare params array to send it to gateway page via POST
	 *
	 * @return array
	 */
	public function getPaymentParams()
	{
		try {
			$params = parent::getPaymentParams();

			$payment_data = Mage::app()->getRequest()->getParam('payment');
			$params["OneClickPayment"] = $payment_data[$this->_code]['one_click_payment'];

			return $params;
		} catch (Exception $e) {
			Mage::throwException($this->_getHelper()->__('validation_invalidCcData'));
		}
	}
}