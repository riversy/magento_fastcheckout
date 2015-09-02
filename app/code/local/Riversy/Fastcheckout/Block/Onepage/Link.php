<?php
class Riversy_Fastcheckout_Block_Onepage_Link extends Mage_Checkout_Block_Onepage_Link
{
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout/onepage/oneclickCheckout', array('_secure'=>true));
    }

    public function getDefaultLocation()
    {
        if ( $location = Mage::helper('fastcheckout')->getLocation()  )
        {
            return $location;
        }
        return Riversy_Fastcheckout_Block_Oneclick_Form::TYPE_LOCAL;
    }

}
