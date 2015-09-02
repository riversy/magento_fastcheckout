<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class WP_Advancedcheckout_CartController extends Mage_Checkout_CartController
{


    public function ajaxBlockAction()
    {
        $data = $this->getRequest()->getParam('data');
        Mage::helper('advancedcheckout')->setOrderReq($data);
        return;
    }

    public function getOrderAction()
    {
        $output = $this->getLayout()->createBlock('advancedcheckout/order')->toHtml();
        $this->getResponse()->setBody($output);
        return;
    }



}



