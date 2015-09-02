<?php
class WP_Advancedcheckout_Block_Onepage_Link extends Mage_Checkout_Block_Onepage_Link
{
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout/onepage/oneclickCheckout', array('_secure'=>true));
    }

    public function getDefaultLocation()
    {
        if ( $location = Mage::helper('advancedcheckout')->getLocation()  )
        {
            return $location;
        }
        return WP_Advancedcheckout_Block_Oneclick_Form::TYPE_LOCAL;
    }

}
