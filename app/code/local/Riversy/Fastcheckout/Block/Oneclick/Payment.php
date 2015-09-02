<?php 
class Riversy_Fastcheckout_Block_Oneclick_Payment extends Mage_Core_Block_Template
{
    const TEMPLATE_PATH_PAYMENT_FORM = 'fastcheckout/form/payment.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH_PAYMENT_FORM);
    }

    public function getEnabled()
    {
        return Mage::getStoreConfig('fastcheckout/payment/enabled');
    }

    public function getCardDescription()
    {
        return Mage::getStoreConfig('fastcheckout/payment/card_description');
    }

    public function getCheckMoLabel()
    {
        return Mage::getStoreConfig('fastcheckout/payment/checkmo_label');
    }

    public function getCardLabel()
    {
        return Mage::getStoreConfig('fastcheckout/payment/card_label');
    }

    public function getPaymentLabel()
    {
        return Mage::getStoreConfig('fastcheckout/payment/payment_label');
    }

    
}