<?php
class WP_Advancedcheckout_Block_Order extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareLayout()
    {      
        $this->setTemplate('advancedcheckout/order.phtml');
        parent::_prepareLayout();
    }

    public function getPrice()
    {
        return Mage::helper('advancedcheckout')->getPrice();
    }

    public function getDate()
    {
        return date('d.m.Y')."г.";
    }

    public function getReq()
    {
        if (Mage::helper('advancedcheckout')->getOrderReq())
        {
            $req = Mage::helper('advancedcheckout')->getOrderReq();
            $req = urldecode($req);
            return $req;
        }        
        return '<br/>';
    }

}
