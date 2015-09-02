<?php

class WP_Advancedcheckout_Block_Adminhtml_Sales_Order_Advanced  extends Mage_Adminhtml_Block_Abstract
{
    const TEMPLATE_PATH = 'advancedcheckout/payment.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
    }

    public function getHistoryHtml()
    {
        $block = $this->getLayout()->createBlock('advancedcheckout/adminhtml_sales_order_history');
        if ($block){
            $block->setParentBlock($this);
            return $block->setOrderId($this->getOrderId())->toHtml();
        }
        
    }
    
    public function getPayment()
    {
        if (!$this->getData('_ac_payent')){            
            $payment = Mage::getModel('advancedcheckout/payment')->load($this->getOrderId(), 'order_id');            
            $this->setData('_ac_payent', $payment);
        }
        return $this->getData('_ac_payent');
    }
    
    public function getPaymentDescription()
    {
        $type = $this->getPayment()->getPaymentType();       
        return Mage::getStoreConfig("advancedcheckout/payment/{$type}_label");
    }
    
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * Get order object
     *
     * @return Mage_Sales_Model_Order
     */
    
    public function getSource()
    {
        if ($this->_order === null) {
            if ($this->hasData('order')) {
                $this->_order = $this->_getData('order');
            } elseif (Mage::registry('current_order')) {
                $this->_order = Mage::registry('current_order');
            } elseif ($this->getParentBlock() && $this->getParentBlock()->getOrder()) {
                $this->_order = $this->getParentBlock()->getOrder();
            } elseif ($orer_id = $this->getRequest()->getParam('order_id')) {
                $this->_order = Mage::getModel('sales/order')->load($orer_id);
            }
        }
        return $this->_order;
    }
    
    public function getTotal()
    {
        return $this->getSource()->getBaseTotalDue();
    }

}

