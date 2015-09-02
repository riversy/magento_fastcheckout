<?php
/**
 * Advanced Checkout Payment
 */
class Riversy_Fastcheckout_Model_Message extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('fastcheckout/message');
    }
    
    public function getDate()
    {
        $dateObj = new Zend_Date($this->getCreatedAt(), Zend_Date::ISO_8601);
        $dateObj->setLocale(Mage::app()->getLocale()->getLocaleCode());        
        return $dateObj->toString(Zend_Date::DATETIME_MEDIUM);
    }
}