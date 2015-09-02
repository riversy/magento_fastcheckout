<?php

class Riversy_Fastcheckout_Model_Mysql4_Payment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('fastcheckout/payment', 'payment_id');
    }
}