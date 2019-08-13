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
class BigFish_PaymentGateway_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_storeName = null;
    protected $_isTestMode = null;
    protected $_apiKey = null;
    protected $_useApi = null;

    const TRANSACTION_STATUS_INITED = 100;
    const TRANSACTION_STATUS_STARTED = 110;
    const TRANSACTION_STATUS_SUCCESS = 120;
    const TRANSACTION_STATUS_CANCELLED = 130;
    const TRANSACTION_STATUS_FAILED = 200;

    /**
     * Get Store name
     *
     * @return string
     */
    public function getStoreName()
    {
        if($this->_storeName === null) {
            $this->_storeName = Mage::getStoreConfig('paymentgateway/settings/storename');
        }
        return $this->_storeName;
    }

    /**
     * Get is test mode
     *
     * @return boolean
     */
    public function getIsTestMode()
    {
        if($this->_isTestMode === null) {
            $this->_isTestMode = Mage::getStoreConfig('paymentgateway/settings/istestmode');
        }
        return (bool)$this->_isTestMode;
    }

    /**
     * Get API key
     *
     * @return string
     */
    public function getApiKey()
    {
        if($this->_apiKey === null) {
            $this->_apiKey = Mage::getStoreConfig('paymentgateway/settings/apikey');
        }
        return $this->_apiKey;
    }

    /**
     * Get use API
     *
     * @return string
     */
    public function getUseApi()
    {
        if($this->_useApi === null) {
            $this->_useApi = Mage::getStoreConfig('paymentgateway/settings/useapi');
        }
        return $this->_useApi;
    }
}

?>
