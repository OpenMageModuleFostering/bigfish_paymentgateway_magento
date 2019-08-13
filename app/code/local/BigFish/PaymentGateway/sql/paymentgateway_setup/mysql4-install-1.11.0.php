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

$this->startSetup();

$this->run("

	-- DROP TABLE IF EXISTS bigfish_paymentgateway;
	CREATE TABLE IF NOT EXISTS bigfish_paymentgateway (
	  `paymentgateway_id` int(11) unsigned NOT NULL auto_increment,
	  `order_id` varchar(255) NOT NULL default '',
	  `transaction_id` varchar(255) NOT NULL default '',
	  `created_time` datetime NULL,
	  `status` smallint(6) NOT NULL default '0',
	  PRIMARY KEY (`paymentgateway_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$this->run("

	-- DROP TABLE IF EXISTS bigfish_paymentgateway_log;
	CREATE TABLE IF NOT EXISTS bigfish_paymentgateway_log (
	  `log_id` int(11) unsigned NOT NULL auto_increment,
	  `paymentgateway_id` int(11) unsigned NOT NULL,
	  `created_time` datetime NULL,
	  `status` smallint(6) NOT NULL default '0',
	  `debug` text NOT NULL default '',
	  PRIMARY KEY (`log_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");



// Deprecated payment method handling
$deprecatedPaymentMethods = array(
	'mpp',
	'mcm',
	'payu',
	'payuwire',
	'payucash',
	'payumobile',
	'barion',
);

if (!empty($deprecatedPaymentMethods)) {
	$setup = new Mage_Core_Model_Config();
	foreach ($deprecatedPaymentMethods as $paymentMethod) {
		$configPath = 'payment/paymentgateway_' . strtolower($paymentMethod) . '/active';
		if (Mage::getStoreConfig($configPath, Mage::app()->getStore()) !== null) {
			$setup->saveConfig($configPath, 0, 'default', 0);
		}
	}
}

$this->endSetup();
?>