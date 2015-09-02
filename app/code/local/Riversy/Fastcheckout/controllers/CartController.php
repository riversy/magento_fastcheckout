<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class Riversy_Fastcheckout_CartController extends Mage_Checkout_CartController
{


    public function ajaxBlockAction()
    {
        $data = $this->getRequest()->getParam('data');
        Mage::helper('fastcheckout')->setOrderReq($data);
        return;
    }

    public function getOrderAction()
    {
        $output = $this->getLayout()->createBlock('fastcheckout/order')->toHtml();
        $this->getResponse()->setBody($output);
        return;
    }
}



