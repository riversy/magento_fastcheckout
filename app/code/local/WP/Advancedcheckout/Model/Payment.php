<?php
/**
 * Advanced Checkout Payment
 */
class WP_Advancedcheckout_Model_Payment extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('advancedcheckout/payment');
    }

    public function addHistoryMessage($text)
    {
        
        if ($this->getId() && $text){
            
            $message = Mage::getModel('advancedcheckout/message');
            $message->setPaymentId($this->getId())
                    ->setCreatedAt(date('Y-m-d H:i:s'))
                    ->setMessage($text)
                    ->save();                        
        }
        return $this;
    }
    
    


}