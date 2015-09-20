<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';

class Riversy_Fastcheckout_OnepageController extends Mage_Checkout_OnepageController
{
    const PATH_SUCCESS = 'checkout/onepage/success';
    const PATH_FAILURE = 'checkout/onepage/failure';

    const RUS_SHIPMENT_METHOD = 'flatrate_flatrate';
    const RUS_FREE_SHIPMENT = 'freeshipping_freeshipping';

    protected $_rusPayment = array('method' => 'checkmo');


    protected $_customer;

    public function getCustomer()
    {
        if (!$this->_customer) {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
                $this->_customer = Mage::getModel('customer/customer')->load($customerId);
            } else {
                return null;
            }
        }
        return $this->_customer;
    }

    /**
     * Address for OneclickCheckout
     */
    protected function _getAddress($addressId)
    {
        if ($addressId) {
            return $this->getOnepage()->getAddress($addressId);
        }
    }

    protected function _saveMethod($method)
    {
        if ($method) {
            $result = $this->getOnepage()->saveCheckoutMethod($method);
        }
        return $this;
    }

    /**
     * save checkout billing address
     */
    protected function _saveAddress($address = array(), $customerAddressId = null)
    {
        if ($address) {
            $result = $this->getOnepage()->saveBilling($address, $customerAddressId);
            $result = $this->getOnepage()->saveShipping($address, $customerAddressId);
        }
        return $this;
    }

    protected function _saveShippingMethod($shipping_method)
    {
        if ($shipping_method) {
            $result = $this->getOnepage()->saveShippingMethod($shipping_method);
            Mage::register('wp_ach_shipping_method', $shipping_method);
        }
        return $this;
    }

    protected function _savePayment($payment)
    {
        if ($payment) {
            $result = $this->getOnepage()->savePayment($payment);
        }
        return $this;
    }

    protected function _saveOrder($comment = null)
    {
        $result = array();
        try {
            $this->getOnepage()->saveOrder($comment);
            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error'] = false;
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();
            $this->getOnepage()->getQuote()->save();
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
            $this->getOnepage()->getQuote()->save();
        }
        return $result;
    }

    /*
     * Create order and put it to magento
     */
    public function oneclickCheckoutAction()
    {
        if ($data = $this->getRequest()->getPost()) {


            Mage::log($this->getOnepage()->getQuote()->getItemsCount());

            if (!$this->getOnepage()->getQuote()->getItemsCount()) {
                $this->_redirect(self::PATH_SUCCESS);
                return;
            }

            if (!isset($data['checkout_method'])) {
                $this->_redirect(self::PATH_FAILURE);
            }
            Mage::helper('fastcheckout')->setLocation($data['checkout_method']);
            if ($data['checkout_method'] === 'local') {
                $data = $data['local'];
            } else {
                $data = $data['other'];
            }
            Mage::helper('fastcheckout')->setFormData($data);
            # Set up method
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $method = Mage_Sales_Model_Quote::CHECKOUT_METHOD_LOGIN_IN;
            } else {
                $method = Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST;
            }
            $this->_saveMethod($method);

            # Set up address
            if ($this->getCustomer()) {
                $shippmentAddress = $this->getCustomer()->getPrimaryShippingAddress();
                $billingAddress = $this->getCustomer()->getPrimaryBillingAddress();
            }
            $address = array();
            if ($this->getCustomer() && ($shippmentAddress)) {

                if ($shippmentAddress) {
                    $result1 = $this->getOnepage()->saveBilling($shippmentAddress, $shippmentAddress->getId());
                }

            }
            if ($this->getCustomer() && ($billingAddress)) {

                if ($billingAddress) {
                    $result2 = $this->getOnepage()->saveShipping($billingAddress, $billingAddress->getId());
                }
            } else {
                $names = explode(' ', $data['name'], 2);
                if (count($names) > 1) {
                    $address['firstname'] = $names[0];
                    $address['lastname'] = $names[1];
                } else {
                    $address['firstname'] = $names[0];
                    $address['lastname'] = '[фамилия не указана]';
                }

                Mage::helper('fastcheckout')->setFormData($data);
                Mage::helper('fastcheckout')->setHasEmail($data['email'] ? true : false);
                $address['email'] = $this->_getArrayVal($data, 'email');
                $address['street'][0] = $this->_getArrayVal($data, 'street');
                $address['street'][1] = $this->_getArrayVal($data, 'street');
                $address['region_id'] = 'нет';
                $address['postcode'] = $this->_getArrayVal($data, 'zip');
                $address['city'] = $this->_getArrayVal($data, 'city');
                $address['country_id'] = $this->_getArrayVal($data, 'country_id');
                $address['telephone'] = $this->_getArrayVal($data, 'phone');
                $address['fax'] = $this->_getArrayVal($data, 'phone');
                $address['save_in_address_book'] = 1;
                $address['use_for_shipping'] = 1;
                $this->_saveAddress($address);
            }
            if (isset($data['comment']) && $data['comment']) {
                $comment = $data['comment'];
            } else {
                $comment = null;
            }

            $express = isset($data['express']);
            $locate = Mage::helper('fastcheckout')->getLocation();
            $subtotal = $this->getOnepage()->getQuote()->getSubtotal();
            $min_subtotal = Mage::helper('fastcheckout')->confDefaultMinPrice();
            $express_tax = Mage::helper('fastcheckout')->confExpressPrice();
            $local_tax = Mage::helper('fastcheckout')->confTax();
            $tax = 0;

            if ($locate === Riversy_Fastcheckout_Block_Oneclick_Form::TYPE_OTHER) {
                $shipment_method = self::RUS_FREE_SHIPMENT;
                $tax = 0;
            } else {
                if (($subtotal >= $min_subtotal) && $express) {
                    $tax = (float)$express_tax;
                    Mage::register('wp_tax', $tax);
                    $shipment_method = self::RUS_SHIPMENT_METHOD;
                } elseif ($subtotal >= $min_subtotal) {
                    $shipment_method = self::RUS_FREE_SHIPMENT;
                } else {
                    $tax = $express ? (float)$express_tax + (float)$local_tax : (float)$local_tax;
                    Mage::register('wp_tax', $tax);
                    $shipment_method = self::RUS_SHIPMENT_METHOD;
                }
            }
            # Set up shilpment action
            $this->_saveShippingMethod($shipment_method);

            # Set up payment
            $this->_savePayment($this->_rusPayment);

            # Save order
            $result = $this->_saveOrder($comment);


            /**
             * Insert Payment Type
             */
            if ($orderId = $this->getOnepage()->getCheckout()->getLastOrderId()) {

                $payment = Mage::getModel('fastcheckout/payment')->load($orderId, 'order_id');
                if (!$payment->getId()) {
                    $payment->setOrderId($orderId)
                        ->setPaymentType(isset($data['payment']) ? $data['payment'] : 'checkmo')
                        ->save();
                }
            }

            # Redirect to proper place
            if ($result['success']) {
                $this->_redirect(self::PATH_SUCCESS);
            } else {
                Mage::getSingleton('core/session')->addError($result['error_messages']);
                $this->_redirect(self::PATH_FAILURE);
            }
        }
    }

    protected function _getArrayVal($data = array(), $key = 'none')
    {
        if (isset($data[$key]) && $data[$key]) {
            return $data[$key];
        } elseif ($key === 'lastname') {
            return '&nbsp';
        } elseif ($key === 'city') {
            return Mage::helper('fastcheckout')->__('Москва');
        } elseif ($key === 'country_id') {
            return 'RU';
        } else {
            return '[не указано]';
        }
    }

    public function indexAction()
    {
        $this
            ->getResponse()
            ->setRedirect(
                Mage::getUrl('checkout/cart/')
            );
    }

}
