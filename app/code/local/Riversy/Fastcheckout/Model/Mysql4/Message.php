<?php

class Riversy_Fastcheckout_Model_Mysql4_Message extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('fastcheckout/message', 'message_id');
    }
}