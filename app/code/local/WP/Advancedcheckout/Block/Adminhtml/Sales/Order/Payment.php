<?php

class WP_Advancedcheckout_Block_Adminhtml_Sales_Order_Payment extends Mage_Adminhtml_Block_Sales_Order_Payment
{
    
    protected function _toHtml()
    {
        $html = '';
        if ($block = $this->getLayout()->createBlock('advancedcheckout/adminhtml_sales_order_advanced')){
            $html .= $block->toHtml();
        }
        return $html;
    }

}
