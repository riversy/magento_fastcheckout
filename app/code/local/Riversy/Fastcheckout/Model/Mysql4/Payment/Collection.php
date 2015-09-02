<?php

class WP_Advanvedcheckout_Model_Mysql4_Payment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('fastcheckout/payment');
    }
}