<?php
class Riversy_Fastcheckout_Model_Observer
{
    public function processOrder($event)
    {
        $order = $event->getOrder();

        $description = $order->getShippingDescription();
        $shipping_method = $order->getShippingMethod();
       
        if ($shipping_method && !$description){
            $shipping_method = explode("_", $shipping_method);
            $shipping_method = $shipping_method[0];            
            $shipping = Mage::getSingleton('shipping/config')->getCarrierInstance($shipping_method);
            if ($shipping){           
                $description = $shipping->getConfigData('title');
                $order->setShippingDescription($description);
                $order->save();
            }
        }
    }
}
    


