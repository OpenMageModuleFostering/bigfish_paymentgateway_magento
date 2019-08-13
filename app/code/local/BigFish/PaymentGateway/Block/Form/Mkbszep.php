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
class BigFish_PaymentGateway_Block_Form_Mkbszep extends BigFish_PaymentGateway_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bfpaymentgateway/form/mkbszep.phtml');
    }

    public function getRedirectMessage()
    {
        return Mage::helper("paymentgateway")->__("redirectMessage");
    }


    public function getCustomMessage()
    {
		$cardPocketIds = BigFish_PaymentGateway_Model_Config::getMkbSzepCafeteriaId();

        return Mage::helper('paymentgateway')->__('The next pocket will be debited at payment:').' <b>'.
            $cardPocketIds[Mage::getStoreConfig('paymentgateway/paymentgateway_mkbszep/mkbszepcafeteriaid')].'</b>';
    }

}