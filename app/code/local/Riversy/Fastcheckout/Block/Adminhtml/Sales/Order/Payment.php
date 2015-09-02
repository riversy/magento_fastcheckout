<?php

class Riversy_Fastcheckout_Block_Adminhtml_Sales_Order_Payment extends Mage_Adminhtml_Block_Sales_Order_Payment
{
    
    protected function _toHtml()
    {
        $html = '';
        if ($block = $this->getLayout()->createBlock('fastcheckout/adminhtml_sales_order_advanced')){
            $html .= $block->toHtml();
        }
        return $html;
    }

}
