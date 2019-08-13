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
class BigFish_PaymentGateway_Block_Form_Otpszep extends BigFish_PaymentGateway_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bfpaymentgateway/form/otpszep.phtml');
    }

    public function getRedirectMessage()
    {
        return Mage::helper("paymentgateway")->__("redirectMessage");
    }
	

    public function getCustomMessage()
    {
		$cardPocketIds = BigFish_PaymentGateway_Model_Config::getOtpCardPocketId();
		
        return Mage::helper('paymentgateway')->__('The next pocket will be debited at payment:').' <b>'.$cardPocketIds[Mage::getStoreConfig('paymentgateway/paymentgateway_otpszep/otpcardpocketid')].'</b>';
    }

}