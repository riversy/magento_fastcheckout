<?php

class WP_Advancedcheckout_Block_Adminhtml_Sales_Order_History  extends WP_Advancedcheckout_Block_Adminhtml_Sales_Order_Advanced
{
    const TEMPLATE_PATH = 'advancedcheckout/history.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);              
    }
    
    protected function _getPaymentId()
    {
        return $this->getPayment()->getId();                
    }
    
    public function getCollection()
    {
        $collection =  Mage::getModel('advancedcheckout/message')->getCollection();       
        $collection->addPaymentFilter($this->_getPaymentId())
                    ->addOrderByDate()
                    ;
        
        return $collection;
    }

}

