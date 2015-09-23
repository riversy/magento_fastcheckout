<?php
class Riversy_Fastcheckout_CartController extends Mage_Core_Controller_Front_Action
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



