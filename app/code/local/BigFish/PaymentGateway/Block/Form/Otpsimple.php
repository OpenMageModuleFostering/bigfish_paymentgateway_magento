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
* @copyright  Copyright (c) 2011, BIG FISH Ltd.
*/
class BigFish_PaymentGateway_Block_Form_OTPSimple extends BigFish_PaymentGateway_Block_Form
{
    protected function _construct()
    {
        parent::_construct();

		$isOneClick = Mage::getStoreConfig('payment/paymentgateway_otpsimple/one_click_payment');
		if (!empty($isOneClick)) {
			$this->setTemplate('bfpaymentgateway/form/oneclickpayment.phtml');
		}
    }

    public function getRedirectMessage()
    {
        return Mage::helper("paymentgateway")->__("redirectMessage");
    }
}