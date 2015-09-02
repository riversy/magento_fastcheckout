<?php

class Riversy_Fastcheckout_Block_Sales_Order_Info extends Mage_Sales_Block_Order_Info
{
    public function getPaymentInfoHtml()
    {
        return $this->getPaymentDescription();
    }
    
    public function getPayment()
    {
        if (!$this->getData('_ac_payent')){            
            $payment = Mage::getModel('fastcheckout/payment')->load($this->getOrderId(), 'order_id');
            $this->setData('_ac_payent', $payment);
        }
        return $this->getData('_ac_payent');
    }
    
    public function getPaymentDescription()
    {
        $type = $this->getPayment()->getPaymentType();       
        return Mage::getStoreConfig("fastcheckout/payment/{$type}_label");
    }
    
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }    
    
}