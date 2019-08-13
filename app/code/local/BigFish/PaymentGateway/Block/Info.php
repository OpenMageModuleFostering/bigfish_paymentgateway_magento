<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Phoenix
 * @package     Phoenix_Moneybookers
 * @copyright   Copyright (c) 2009 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BigFish_PaymentGateway_Block_Info extends Mage_Payment_Block_Info
{
    /**
     * Constructor. Set template.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bfpaymentgateway/info.phtml');
    }

    /**
     * Returns code of payment method
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->getInfo()->getMethodInstance()->getCode();
    }

    /**
     * Returns info of OTP2 payment method
     *
     * @return array
     */
    public function getOtp2WayInfo()
    {
        $info = $this->getInfo();
        $data = array();
        $data["cc_number"]="xxxx-".substr($info->getCcNumber(),-4);
        $data["cc_exp"]=str_pad($info->getCcExpMonth(), 2, STR_PAD_LEFT, '0')."/".substr($info->getCcExpYear(),2,2);
        return $data;
    }

    /**
     * Build PDF content of info block
     *
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('bfpaymentgateway/pdf/info.phtml');
        return $this->toHtml();
    }
}
