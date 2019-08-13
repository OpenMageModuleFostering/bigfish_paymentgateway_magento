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
class BigFish_PaymentGateway_Model_Otpszep extends BigFish_PaymentGateway_Model_Abstract
{
	protected $_formBlockType = 'paymentgateway/form_otpszep';
	
    protected $_code  = 'paymentgateway_otpszep';

    protected $_paymentMethod = 'OTPSZEP';

    public function getPaymentParams()
    {
        try {
            $params = parent::getPaymentParams();

            $params['OtpCardPocketId']=Mage::getStoreConfig('paymentgateway/paymentgateway_otpszep/otpcardpocketid');
			
			if (!strlen($params['OtpCardPocketId'])) {
				throw new Exception();
			}

            return $params;
        } catch (Exception $e) {
            Mage::throwException($this->_getHelper()->__('validation_invalidPocketId'));
        }
    }
}