<?php

class BigFish_PaymentGateway_Model_Config
{
    /**
     * PaymentGateway USE API
     *
     * @return array
     */
    public function getApiType()
    {
        return array(
            'REST' => Mage::helper('paymentgateway')->__('HTTP REST API (Default)'),
        );
    }

    /**
     * PaymentGateway KHB Pocket Ids
     *
     * @return array
     */
    public function getKhbCardPocketId()
    {
        return array(
            '' => Mage::helper('paymentgateway')->__('Please, select a pocket.'),
            '1' => Mage::helper('paymentgateway')->__('Accommodation'),
            '2' => Mage::helper('paymentgateway')->__('Hospitality'),
            '3' => Mage::helper('paymentgateway')->__('Leisure')
        );
    }
	
    /**
     * PaymentGateway MKB Pocket Ids
     *
     * @return array
     */
    public function getMkbSzepCafeteriaId()
    {
        return array(
            '' => Mage::helper('paymentgateway')->__('Please, select a pocket.'),
            '1111' => Mage::helper('paymentgateway')->__('Accommodation'),
            '2222' => Mage::helper('paymentgateway')->__('Hospitality'),
            '3333' => Mage::helper('paymentgateway')->__('Leisure')
        );
    }
	
    /**
     * PaymentGateway OTP Pocket Ids
     *
     * @return array
     */
    public function getOtpCardPocketId()
    {
        return array(
            '' => Mage::helper('paymentgateway')->__('Please, select a pocket.'),
            '09' => Mage::helper('paymentgateway')->__('Accommodation'),
            '07' => Mage::helper('paymentgateway')->__('Hospitality'),
            '08' => Mage::helper('paymentgateway')->__('Leisure')
        );
    }

    /**
     * PaymentGateway Saferpay Payment Methods
     *
     * @return array
     */
    public function getSaferpayPaymentMethods()
    {
        $saferpay = new BigFish_PaymentGateway_Model_Saferpay();
        return $saferpay->getPaymentMethods();
    }

    /**
     * PaymentGateway Saferpay Wallets
     *
     * @return array
     */
    public function getSaferpayWallets()
    {
        $saferpay = new BigFish_PaymentGateway_Model_Saferpay();
        return $saferpay->getWallets();
    }

    /**
     * PaymentGateway QPAY Payment Types
     *
     * @return array
     */
    public function getQpayPaymentTypes()
    {
        $qpay = new BigFish_PaymentGateway_Model_Wirecard();
        return $qpay->getPaymentTypes();
    }
}

