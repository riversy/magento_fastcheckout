<?php
class Riversy_Fastcheckout_Block_Cart extends Mage_Checkout_Block_Cart
{
    protected $_block = null;

    protected function _toHtml()
    {     
        $totals = $this->getTotals();
                        
        $total = $totals['grand_total']->getValue();
        $address = $totals['grand_total']->getAddress();
        $subtotal = $totals['subtotal']->getValue();
        if ( $this->getQuote()->getSubtotal() < Mage::helper('fastcheckout')->confDefaultMinPrice() )
        {
            $obj = Mage::getModel('sales/quote_address_total');
            $obj->setCode('shipping');
            $obj->setTitle('Доставка');
            $obj->setValue( Mage::helper('fastcheckout')->confTax() );
            $obj->setAddress($address);
            $address->setShippingAmount(Mage::helper('fastcheckout')->confTax());
            $totals['shipping'] = $obj;
            $total = $totals['grand_total']->getValue();
            $totals['grand_total']->setValue($total + Mage::helper('fastcheckout')->confTax() );
            $total = $total + Mage::helper('fastcheckout')->confTax();
        }

        $obj = Mage::getModel('sales/quote_address_total');
        $obj->setCode('other_total');
        $obj->setTitle('Итоговая сумма:');
        $obj->setValue( $subtotal );
        $obj->setArea('other_footer');
        $obj->setAddress($address);
        $totals['other_total'] = $obj;

        $this->setMyTotals($totals);
        Mage::helper('fastcheckout')->setPrice($subtotal.' руб.');

        return parent::_toHtml();
    }

    public function getTemplate()
    {
        if ($this->getQuote()->getItemsCount()) {
            return 'fastcheckout/cart.phtml';
        } else {
            return $this->getEmptyTemplate();
        }
    }

    public function getDefaultLocation()
    {
        if ( $location = Mage::helper('fastcheckout')->getLocation()  ){
            return $location;
        }
        return Riversy_Fastcheckout_Block_Form::TYPE_LOCAL;
    }

    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout/onepage/oneclickCheckout', array('_secure'=>true));
    }

    public function getFormHtml($location = Riversy_Fastcheckout_Block_Form::TEMPLATE_LOCAL)
    {
        $block = $this->getLayout()
                    ->createBlock('fastcheckout/form')
                    ->setLocation($location)
                    ->setParentBlock($this)
                    ;
        
        return $block->toHtml();
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/type_onepage')->getQuote();
    }
    
    public function setMyTotals($value)
    {
        Mage::helper('fastcheckout')->setTotals($value);
        return $this;
    }

    public function getTotals()
    {
        return $this->getQuote()->getTotals();
    }

}


