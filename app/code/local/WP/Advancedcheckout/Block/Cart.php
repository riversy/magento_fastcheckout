<?php
class WP_Advancedcheckout_Block_Cart extends Mage_Checkout_Block_Cart
{
    protected $_block = null;
    
    public function __construct()
    {
        parent::__construct();
        
    }

    protected function _toHtml()
    {     
        $totals = $this->getTotals();
                        
        $total = $totals['grand_total']->getValue();
        $address = $totals['grand_total']->getAddress();
        $subtotal = $totals['subtotal']->getValue();
        if ( $this->getQuote()->getSubtotal() < Mage::helper('advancedcheckout')->confDefaultMinPrice() )
        {
            $obj = Mage::getModel('sales/quote_address_total');
            $obj->setCode('shipping');
            $obj->setTitle('Доставка');
            $obj->setValue( Mage::helper('advancedcheckout')->confTax() );
            $obj->setAddress($address);
            $address->setShippingAmount(Mage::helper('advancedcheckout')->confTax());
            $totals['shipping'] = $obj;
            $total = $totals['grand_total']->getValue();
            $totals['grand_total']->setValue($total + Mage::helper('advancedcheckout')->confTax() );
            $total = $total + Mage::helper('advancedcheckout')->confTax();
        }

//        $obj = new Mage_Sales_Model_Quote_Address_Total();
//        $obj->setCode('subtotal');
//        $obj->setTitle('Предварительная сумма');
//        $obj->setValue( $subtotal );
//        $obj->setArea('other_body');
//        $totals['other_subtotal'] = $obj;

//        $obj = new Mage_Sales_Model_Quote_Address_Total();
//        $obj->setCode('other_shipping');
//        $obj->setTitle('Доствака');
//        $obj->setValue( 'расчитывается отдельно' );
//        $obj->setArea('other_body');
//        $totals['other_shipping'] = $obj;
        
        $obj = Mage::getModel('sales/quote_address_total');
        $obj->setCode('other_total');
        $obj->setTitle('Итоговая сумма:');
        $obj->setValue( $subtotal );
        $obj->setArea('other_footer');
        $obj->setAddress($address);
        $totals['other_total'] = $obj;

        $this->setMyTotals($totals);
        Mage::helper('advancedcheckout')->setPrice($subtotal.' руб.');

        return parent::_toHtml();
    }

    public function getTemplate()
    {
        if ($this->getQuote()->getItemsCount()) {
            return 'advancedcheckout/cart.phtml';
        } else {
            return $this->getEmptyTemplate();
        }
    }

    public function getDefaultLocation()
    {
        if ( $location = Mage::helper('advancedcheckout')->getLocation()  )
        {
            return $location;
        }
        return WP_Advancedcheckout_Block_Oneclick_Form::TYPE_LOCAL;
    }

    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout/onepage/oneclickCheckout', array('_secure'=>true));
    }

    public function getFormHtml($location = WP_Advancedcheckout_Block_Oneclick_Form::TEMPLATE_LOCAL)
    {
        $block = $this->getLayout()
                    ->createBlock('advancedcheckout/oneclick_form')
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
        Mage::helper('advancedcheckout')->setTotals($value);
        return $this;
    }

    public function getTotals()
    {
        return $this->getQuote()->getTotals();
    }

    public function getPaymentFormHtml($location = WP_Advancedcheckout_Block_Oneclick_Form::TYPE_LOCAL)
    {
        $block = $this->getLayout()->createBlock('advancedcheckout/oneclick_payment')->setParentBlock($this);
        if ($block){
            $this->_block = $block;

        }
        $this->_block->setPlace($location);
        return $this->_block->toHtml();
    }
}


