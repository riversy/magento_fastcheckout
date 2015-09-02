<?php
class WP_Advancedcheckout_Block_Oneclick_Form extends Mage_Core_Block_Template
{
    const TYPE_LOCAL = 'local';
    const TYPE_OTHER = 'other';
    const TEMPLATE_LOCAL = 'advancedcheckout/form/local.phtml';
    const TEMPLATE_OTHER = 'advancedcheckout/form/other.phtml';
    
    
    private $_location;
    private $_countryCollection;
    private $_customer;

    public function setLocation($value)
    {
        $this->_location = $value;
        if ( $this->getLocation() === self::TYPE_LOCAL )
        {
            $this->setTemplate(self::TEMPLATE_LOCAL);
        }
        elseif( $this->getLocation() === self::TYPE_OTHER  )
        {
            $this->setTemplate(self::TEMPLATE_OTHER);
        }
        return $this;
    }

    public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }

    public function getLocation()
    {
        return $this->_location;
    }

    public function getDefaultCountryId()
    {
        return 'RU';
    }

    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getCountryId();
        if (is_null($countryId)) {
            $countryId = $this->getDefaultCountryId();
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setStyle('width: 12em;')
            ->setOptions($this->getCountryOptions());
        if ($type === 'shipping') {
            $select->setExtraParams('onchange="shipping.setSameAsBilling(false);"');
        }
        return $select->getHtml();
    }
    

    public function getCountryOptions()
    {
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }

    /*
     * Predefined values part
     */
    public function isUserLogged()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function getCustomer()
    {
        if (!$this->_customer)
        {
            if ($this->isUserLogged())
            {                
                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
                $this->_customer = Mage::getModel('customer/customer')->load($customerId);                
            }
            else
            {
                return null;
            }
        }
        return $this->_customer;
    }

    protected function _getFormValue($key)
    {
        if ($data = Mage::helper('advancedcheckout')->getFormData())
        {
            if (isset($data[$key]))
            {
                return $data[$key];
            }
        }
    }

    public function getEmail()
    {
        if ($customer = $this->getCustomer())
        {
            return $customer->getEmail();
        }
        return $this->_getFormValue('email');
    }

    public function getName()
    {
        if ($customer = $this->getCustomer())
        {
            return $customer->getName();
        }
        return $this->_getFormValue('name');
    }

    public function getStreet()
    {
        if (($customer = $this->getCustomer()) && $customer->getDefaultShippingAddress())
        {  
            $street = $customer->getDefaultShippingAddress()->getStreet();
            return $street[0];
        }
        return $this->_getFormValue('street');
    }

    public function getCity()
    {
        if (($customer = $this->getCustomer()) && $customer->getDefaultShippingAddress())
        {
            return $customer->getDefaultShippingAddress()->getCity();
        }
        return $this->_getFormValue('city');
    }

    public function getPhone()
    {
        if (($customer = $this->getCustomer()) && $customer->getDefaultShippingAddress())
        {
            return $customer->getDefaultShippingAddress()->getTelephone();
        }
        return $this->_getFormValue('phone');
    }

    public function getCountryId()
    {
        if (($customer = $this->getCustomer()) && $customer->getDefaultShippingAddress())
        {
           return $customer->getDefaultShippingAddress()->getCountryId();
        }
        return $this->_getFormValue('country_id') ? $this->_getFormValue('country_id') : 'RU';
    }
    
    public function getComment()
    {
        return $this->_getFormValue('comment');
    }

    public function getZip()
    {
        if (($customer = $this->getCustomer()) && $customer->getDefaultShippingAddress())
        {
           return $customer->getDefaultShippingAddress()->getPostcode();
        }
        return $this->_getFormValue('zip');
    }

    public function getOrderLink()
    {
        return $this->getUrl('advancedcheckout/cart/getOrder');
    }

    protected function _toHtml()
    {
//        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
//        Mage::helper('advancedcheckout')->setPrice( $totals['subtotal']->getValue().' руб.' );
        return parent::_toHtml();
    }


    public function getSubtotal()
    {
        return Mage::getSingleton('checkout/type_onepage')->getQuote()->getSubtotal();
    }

    public function showNotification()
    {
        return ( $this->getSubtotal() < Mage::helper('advancedcheckout')->confDefaultMinPrice() );
    }
    
    public function getMinSubtotal()
    {
        return Mage::helper('advancedcheckout')->confDefaultMinPrice();
    }
    
    public function getShippingCost()
    {
        return Mage::helper('advancedcheckout')->confTax();
    }
}
