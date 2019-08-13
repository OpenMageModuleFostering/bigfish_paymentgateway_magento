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
class BigFish_PaymentGateway_Model_Khbszep extends BigFish_PaymentGateway_Model_Abstract
{
	protected $_formBlockType = 'paymentgateway/form_khbszep';
	
    protected $_code  = 'paymentgateway_khbszep';

    protected $_paymentMethod = 'KHBSZEP';

    public function getPaymentParams()
    {
        try {
            $params = parent::getPaymentParams();

            $params['extra']['KhbCardPocketId']=Mage::getStoreConfig('paymentgateway/paymentgateway_khbszep/khbcardpocketid');
			
			if (!strlen($params['extra']['KhbCardPocketId'])) {
				throw new Exception();
			}

            return $params;
        } catch (Exception $e) {
            Mage::throwException($this->_getHelper()->__('validation_invalidPocketId'));
        }
    }
}