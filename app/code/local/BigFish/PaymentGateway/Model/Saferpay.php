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
class BigFish_PaymentGateway_Model_Saferpay extends BigFish_PaymentGateway_Model_Abstract
{
	protected $_formBlockType = 'paymentgateway/form_saferpay';

    protected $_code  = 'paymentgateway_saferpay';

    protected $_paymentMethod = 'Saferpay';

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
			$params['OneClickPayment'] = $payment_data[$this->_code]['one_click_payment'];

			$extra = array(
				'SaferpayPaymentMethods' => $payment_data[$this->_code]['payment_methods'],
				'SaferpayWallets' => $payment_data[$this->_code]['wallets']
			);
			$params['extra'] = $extra;

			return $params;
		} catch (Exception $e) {
			Mage::throwException($this->_getHelper()->__('validation_invalidCcData'));
		}
	}

	/**
	 * Get Saferpay payment methods
	 * @return array
	 */
	public function getPaymentMethods()
	{
		return array (
			array (
				'value' => 'AMEX',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_amex')
			),
			array (
				'value' => 'DIRECTDEBIT',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_directdebit')
			),
			array (
				'value' => 'INVOICE',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_invoice')
			),
			array (
				'value' => 'BONUS',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_bonus')
			),
			array (
				'value' => 'DINERS',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_diners')
			),
			array (
				'value' => 'EPRZELEWY',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_eprzelewy')
			),
			array (
				'value' => 'EPS',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_eps')
			),
			array (
				'value' => 'GIROPAY',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_giropay')
			),
			array (
				'value' => 'IDEAL',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_ideal')
			),
			array (
				'value' => 'JCB',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_jcb')),
			array (
				'value' => 'MAESTRO',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_maestro')
			),
			array (
				'value' => 'MASTERCARD',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_mastercard')
			),
			array (
				'value' => 'MYONE',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_myone')
			),
			array (
				'value' => 'PAYPAL',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_paypal')
			),
			array (
				'value' => 'POSTCARD',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_postcard')
			),
			array (
				'value' => 'POSTFINANCE',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_postfinance')
			),
			array (
				'value' => 'SAFERPAYTEST',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_saferpaytest')
			),
			array (
				'value' => 'SOFORT',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_sofort')
			),
			array (
				'value' => 'VISA',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_visa')
			),
			array (
				'value' => 'VPAY',
				'label' => Mage::helper('paymentgateway')->__('saferpay_payment_method_vpay')
			),
		);
	}

	/**
	 * Get Saferpay wallets
	 * @return array
	 */
	public function getWallets()
	{
		return array (
			array (
				'value' => 'MASTERPASS',
				'label' => Mage::helper('paymentgateway')->__('saferpay_wallet_masterpass')
			)
		);
	}

}