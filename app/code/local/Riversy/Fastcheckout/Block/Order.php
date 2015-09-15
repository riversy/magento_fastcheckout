<?php

class Riversy_Fastcheckout_Block_Order extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareLayout()
    {
        $this->setTemplate('fastcheckout/order.phtml');
        parent::_prepareLayout();
    }

    public function getPrice()
    {
        return Mage::helper('fastcheckout')->getPrice();
    }

    public function getDate()
    {
        return date('d.m.Y') . "г.";
    }

    public function getReq()
    {
        if (Mage::helper('fastcheckout')->getOrderReq()) {

            $req = Mage::helper('fastcheckout')->getOrderReq();
            $req = urldecode($req);
            return $req;
        }
        return '<br/>';
    }

}
