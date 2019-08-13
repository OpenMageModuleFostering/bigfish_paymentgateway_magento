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
class BigFish_PaymentGateway_Model_Otp2 extends BigFish_PaymentGateway_Model_Abstract
{

    protected $_formBlockType = 'paymentgateway/form_otp2';

    protected $_code  = 'paymentgateway_otp2';
    protected $_paymentMethod = 'OTP2';

    /**
     * Validates credit card informations
     *
     * @return object
     */
    public function validate() {
        parent::validate();

        $info = $this->getInfoInstance();
        $errorMsg = false;

        $ccNumber = $info->getCcNumber();
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);

        if (!$this->validateCcNum($ccNumber)) {
            Mage::throwException($this->_getHelper()->__("validation_invalidCcNumber"));
        }

        if (!$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            Mage::throwException($this->_getHelper()->__("validation_invalidExpDate"));
        }
        return $this;
    }

    /**
     * prepare params array to send it to gateway page via POST
     *
     * @return array
     */
    public function getPaymentParams()
    {
        try {
            $params = parent::getPaymentParams();

            $info = $this->getInfoInstance();

            $params["OtpCardNumber"]=$info->getCcNumber();
            $params["OtpExpiration"]=str_pad($info->getCcExpMonth(), 2, STR_PAD_LEFT, '0').substr($info->getCcExpYear(),2,2);
            $params["OtpCvc"]=$info->getCcCid();
            $params["publicKey"]=Mage::getStoreConfig("payment/paymentgateway_otp2/publickey");

            return $params;
        } catch (Exception $e) {
            Mage::throwException($this->_getHelper()->__('validation_invalidCcData'));
        }
    }

    /**
     * Validate credit card number
     *
     * @param   string $cc_number
     * @return  bool
     */
    public function validateCcNum($ccNumber)
    {
        $cardNumber = strrev($ccNumber);
        $numSum = 0;

        for ($i=0; $i<strlen($cardNumber); $i++) {
            $currentNum = substr($cardNumber, $i, 1);

            /**
             * Double every second digit
             */
            if ($i % 2 == 1) {
                $currentNum *= 2;
            }

            /**
             * Add digits of 2-digit numbers together
             */
            if ($currentNum > 9) {
                $firstNum = $currentNum % 10;
                $secondNum = ($currentNum - $firstNum) / 10;
                $currentNum = $firstNum + $secondNum;
            }

            $numSum += $currentNum;
        }

        /**
         * If the total has no remainder it's OK
         */
        return ($numSum % 10 == 0);
    }

    /**
     * Validate credit card expiration number
     *
     * @param   string $expYear
     * @param   string $expMonth
     * @return  bool
     */
    protected function _validateExpDate($expYear, $expMonth)
    {
        $date = Mage::app()->getLocale()->date();
        if (!$expYear || !$expMonth || ($date->compareYear($expYear)==1) || ($date->compareYear($expYear) == 0 && ($date->compareMonth($expMonth)==1 )  )) {
            return false;
        }
        return true;
    }

}