<?php 
class WP_Advancedcheckout_Block_Oneclick_Payment extends Mage_Core_Block_Template
{
    const TEMPLATE_PATH_PAYMENT_FORM = 'advancedcheckout/form/payment.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH_PAYMENT_FORM);
    }

    public function getEnabled()
    {
        return Mage::getStoreConfig('advancedcheckout/payment/enabled');
    }

    public function getCardDescription()
    {
        return Mage::getStoreConfig('advancedcheckout/payment/card_description');
    }

    public function getCheckMoLabel()
    {
        return Mage::getStoreConfig('advancedcheckout/payment/checkmo_label');
    }

    public function getCardLabel()
    {
        return Mage::getStoreConfig('advancedcheckout/payment/card_label');
    }

    public function getPaymentLabel()
    {
        return Mage::getStoreConfig('advancedcheckout/payment/payment_label');
    }

    
}