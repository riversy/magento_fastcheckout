<?php

class WP_Advancedcheckout_Model_Mysql4_Message_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('advancedcheckout/message');
    }
    
    public function addPaymentFilter($paymentId)
    {
        $this->getSelect()->where("main_table.payment_id = ?", $paymentId);
        return $this;
    }
    
    public function addOrderByDate()
    {
        $this->getSelect()->order("main_table.created_at ASC");
        return $this;
    }
}