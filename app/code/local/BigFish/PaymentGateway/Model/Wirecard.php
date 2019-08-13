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
class BigFish_PaymentGateway_Model_Wirecard extends BigFish_PaymentGateway_Model_Abstract
{
    protected $_formBlockType = 'paymentgateway/form_wirecard';
    protected $_code  = 'paymentgateway_wirecard';

    protected $_paymentMethod = 'QPAY';

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

            $extra = array(
                'QpayPaymentType' => $payment_data[$this->_code]['payment_type']
            );
            $params['extra'] = $extra;

            return $params;
        } catch (Exception $e) {
            Mage::throwException($this->_getHelper()->__('validation_invalidCcData'));
        }
    }

    /**
     * @return array
     */
    public function getPaymentTypes() {
        return array(
            'SELECT'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_select'),
			'BANCONTACT_MISTERCASH'	=> Mage::helper('paymentgateway')->__('qpay_payment_type_bancontact_mistercash'),
            'CCARD'					=> Mage::helper('paymentgateway')->__('qpay_payment_type_ccard'),
            'CCARD-MOTO'			=> Mage::helper('paymentgateway')->__('qpay_payment_type_ccard_moto'),
            'EKONTO'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_ekonto'),
            'EPAY_BG'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_epay_bg'),
            'EPS'					=> Mage::helper('paymentgateway')->__('qpay_payment_type_eps'),
            'GIROPAY'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_giropay'),
            'IDL'					=> Mage::helper('paymentgateway')->__('qpay_payment_type_idl'),
            'MONETA'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_moneta'),
            'MPASS'					=> Mage::helper('paymentgateway')->__('qpay_payment_type_mpass'),
            'PRZELEWY24'			=> Mage::helper('paymentgateway')->__('qpay_payment_type_przelewy24'),
            'PAYPAL'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_paypal'),
            'PBX'					=> Mage::helper('paymentgateway')->__('qpay_payment_type_pbx'),
            'POLI'					=> Mage::helper('paymentgateway')->__('qpay_payment_type_poli'),
            'PSC'					=> Mage::helper('paymentgateway')->__('qpay_payment_type_psc'),
            'QUICK'					=> Mage::helper('paymentgateway')->__('qpay_payment_type_quick'),
            'SEPA-DD'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_sepa_dd'),
            'SKRILLDIRECT'			=> Mage::helper('paymentgateway')->__('qpay_payment_type_skrilldirect'),
            'SKRILLWALLET'			=> Mage::helper('paymentgateway')->__('qpay_payment_type_skrillwallet'),
            'SOFORTUEBERWEISUNG'	=> Mage::helper('paymentgateway')->__('qpay_payment_type_sofortueberweisung'),
            'TATRAPAY'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_tatrapay'),
            'TRUSTLY'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_trustly'),
            'TRUSTPAY'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_trustpay'),
            'VOUCHER'				=> Mage::helper('paymentgateway')->__('qpay_payment_type_voucher'),
        );
    }

}