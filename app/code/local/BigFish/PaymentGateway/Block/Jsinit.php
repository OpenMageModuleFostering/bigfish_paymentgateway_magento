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
class BigFish_PaymentGateway_Block_Jsinit extends Mage_Adminhtml_Block_Template
{
    /**
     * Include JS in head if section is paymentgateway
     */
    protected function _prepareLayout()
    {
        $section = $this->getAction()->getRequest()->getParam('section', false);
        if ($section == 'paymentgateway') {
            $this->getLayout()
                ->getBlock('head')
                ->addJs('mage/adminhtml/bfpaymentgateway.js');
        }
        parent::_prepareLayout();
    }

    /**
     * Print init JS script into body
     * @return string
     */
    protected function _toHtml()
    {
        $section = $this->getAction()->getRequest()->getParam('section', false);
        if ($section == 'paymentgateway') {
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
