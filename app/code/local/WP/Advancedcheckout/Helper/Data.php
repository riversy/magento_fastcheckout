<?php
class WP_Advancedcheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    /*
     * Common config
     */
    public function confDefaultMinPrice()
    {
        return Mage::getStoreConfig('advancedcheckout/options/min_price');
    }

    public function confTax()
    {
        return Mage::getStoreConfig('advancedcheckout/options/tax');
    }

    public function confExpressEnabled()
    {
        return Mage::getStoreConfig('advancedcheckout/options/express_enable');
    }

    public function confExpressPrice()
    {
        return Mage::getStoreConfig('advancedcheckout/options/express_price');
    }

    public function getLocation()
    {
        return Mage::getSingleton('customer/session' )->getWPLocation();
    }

    public function setLocation($value)
    {
        $session = Mage::getSingleton('customer/session' );
        $session->setWPLocation( $value );
        return $this;
    }
    
    public function setPrice($value)
    {
        $session = Mage::getSingleton('customer/session' );
        $session->setWPPrice( $value );
        return $this;        
    }
    public function getPrice()
    {
        return Mage::getSingleton('customer/session' )->getWPPrice();
    }

    public function setHasEmail($value)
    {
        $session = Mage::getSingleton('customer/session' );
        $session->setHasEmail( $value );
        return $this;
    }
    public function getHasEmail()
    {
        return Mage::getSingleton('customer/session' )->getHasEmail();
    }
    
    public function getFormData()
    {
        return Mage::getSingleton('customer/session')->getFormData();
    }

    public function setFormData($value)
    {
        $session = Mage::getSingleton('customer/session');
        $session->setFormData( $value );
        return $this;
    }

    public function setTotals($value)
    {
        Mage::register('wp_ach_totals', $value);
        return $this;        
    }

    public function getTotals()
    {
        return Mage::registry('wp_ach_totals');
    }
    
    public function getOrderReq()
    {
        return Mage::getSingleton('customer/session' )->getOrderReq();
    }

    public function setOrderReq($value)
    {
        $session = Mage::getSingleton('customer/session' );
        $session->setOrderReq( $value );
        return $this;        
    }    
    
    public function isDebud()
    {
        return Mage::getStoreConfig('advancedcheckout/a1pay_a1paymodel/is_debug');
    }
}