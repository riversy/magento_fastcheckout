<?php
class WP_Advancedcheckout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    
    public function saveOrder( $comment = null )
    {
        $this->validate();
        $billing = $this->getQuote()->getBillingAddress();
        if (!$this->getQuote()->isVirtual()) {
            $shipping = $this->getQuote()->getShippingAddress();
        }
        switch ($this->getQuote()->getCheckoutMethod()) {
        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
            if (!$this->getQuote()->isAllowedGuestCheckout()) {
                Mage::throwException(Mage::helper('checkout')->__('Sorry, guest checkout is not enabled. Please try again or contact store owner.'));
            }
            $this->getQuote()->setCustomerId(null)
                ->setCustomerEmail($billing->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
            break;

        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
            $customer = Mage::getModel('customer/customer');
            /* @var $customer Mage_Customer_Model_Customer */

            $customerBilling = $billing->exportCustomerAddress();
            $customer->addAddress($customerBilling);

            if (!$this->getQuote()->isVirtual() && !$shipping->getSameAsBilling()) {
                $customerShipping = $shipping->exportCustomerAddress();
                $customer->addAddress($customerShipping);
            }

            if ($this->getQuote()->getCustomerDob() && !$billing->getCustomerDob()) {
                $billing->setCustomerDob($this->getQuote()->getCustomerDob());
            }

            if ($this->getQuote()->getCustomerTaxvat() && !$billing->getCustomerTaxvat()) {
                $billing->setCustomerTaxvat($this->getQuote()->getCustomerTaxvat());
            }

            Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);

            $customer->setPassword($customer->decryptPassword($this->getQuote()->getPasswordHash()));
            $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));

            $this->getQuote()->setCustomer($customer);
            break;

        default:
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
                $customerBilling = $billing->exportCustomerAddress();
                $customer->addAddress($customerBilling);
            }
            if (!$this->getQuote()->isVirtual() &&
                ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling()) ||
                (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {

                $customerShipping = $shipping->exportCustomerAddress();
                $customer->addAddress($customerShipping);
            }
            $customer->setSavedFromQuote(true);
            $customer->save();

            $changed = false;
            if (isset($customerBilling) && !$customer->getDefaultBilling()) {
                $customer->setDefaultBilling($customerBilling->getId());
                $changed = true;
            }
            if (!$this->getQuote()->isVirtual() && isset($customerBilling) && !$customer->getDefaultShipping() && $shipping->getSameAsBilling()) {
                $customer->setDefaultShipping($customerBilling->getId());
                $changed = true;
            }
            elseif (!$this->getQuote()->isVirtual() && isset($customerShipping) && !$customer->getDefaultShipping()){
                $customer->setDefaultShipping($customerShipping->getId());
                $changed = true;
            }

            if ($changed) {
                $customer->save();
            }
        }

        $this->getQuote()->reserveOrderId();
        $convertQuote = Mage::getModel('sales/convert_quote');
        /* @var $convertQuote Mage_Sales_Model_Convert_Quote */
        //$order = Mage::getModel('sales/order');
        if ($this->getQuote()->isVirtual()) {
            $order = $convertQuote->addressToOrder($billing);
        }
        else {
            $order = $convertQuote->addressToOrder($shipping);
        }
        /* @var $order Mage_Sales_Model_Order */
        $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));

        if (!$this->getQuote()->isVirtual()) {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
        }

        $order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));

        foreach ($this->getQuote()->getAllItems() as $item) {
            $orderItem = $convertQuote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$this->getQuote()));
        // check again, if customer exists
        if ($this->getQuote()->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER) {
            if ($this->_customerEmailExists($customer->getEmail(), Mage::app()->getWebsite()->getId())) {
                Mage::throwException(Mage::helper('checkout')->__('There is already a customer registered using this email address'));
            }
        }
        $order->place();

        if ($this->getQuote()->getCheckoutMethod()==Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER) {
            $customer->save();
            $customerBillingId = $customerBilling->getId();
            if (!$this->getQuote()->isVirtual()) {
                $customerShippingId = isset($customerShipping) ? $customerShipping->getId() : $customerBillingId;
                $customer->setDefaultShipping($customerShippingId);
            }
            $customer->setDefaultBilling($customerBillingId);
            $customer->save();

            $this->getQuote()->setCustomerId($customer->getId());

            $order->setCustomerId($customer->getId());
            Mage::helper('core')->copyFieldset('customer_account', 'to_order', $customer, $order);

            $billing->setCustomerId($customer->getId())->setCustomerAddressId($customerBillingId);
            if (!$this->getQuote()->isVirtual()) {
                $shipping->setCustomerId($customer->getId())->setCustomerAddressId($customerShippingId);
            }

            if ($customer->isConfirmationRequired()) {
                $customer->sendNewAccountEmail('confirmation');
            }
            else {
                $customer->sendNewAccountEmail();
            }
        }

        /**
         * a flag to set that there will be redirect to third party after confirmation
         * eg: paypal standard ipn
         */
        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        if(!$redirectUrl){
            $order->setEmailSent(true);
        }
        
        if ($tax = Mage::registry('wp_tax'))
        {
            $grand_total = $this->getQuote()->getGrandTotal();
            $base_grand_total = $this->getQuote()->getBaseGrandTotal();

            $grand_total += $tax;
            $base_grand_total += $tax;

            $order->setGrandTotal($grand_total);
            $order->setBaseGrandTotal($base_grand_total);
            $order->setBaseShippingAmoun($tax);
            $order->setShippingAmount($tax);
        }

        $shipping = Mage::registry('wp_ach_shipping_method');
        if ($shipping){
            $order->setShippingMethod($shipping);
        }
        
        if ($comment)
        {
            $order->addStatusToHistory('pending', $comment);
        }
        $order->save();

        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

        /**
         * need to have somelogic to set order as new status to make sure order is not finished yet
         * quote will be still active when we send the customer to paypal
         */

        $orderId = $order->getIncrementId();
        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId());
        $this->getCheckout()->setLastOrderId($order->getId());
        $this->getCheckout()->setLastRealOrderId($order->getIncrementId());
        $this->getCheckout()->setRedirectUrl($redirectUrl);

        /**
         * we only want to send to customer about new order when there is no redirect to third party
         */
        if(!$redirectUrl){
            $order->sendNewOrderEmail();
        }

        if ($this->getQuote()->getCheckoutMethod(true)==Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER
            && !Mage::getSingleton('customer/session')->isLoggedIn()) {
            /**
             * we need to save quote here to have it saved with Customer Id.
             * so when loginById() executes checkout/session method loadCustomerQuote
             * it would not create new quotes and merge it with old one.
             */
            $this->getQuote()->save();
            if ($customer->isConfirmationRequired()) {
                Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.',
                    Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())
                ));
            }
            else {
                Mage::getSingleton('customer/session')->loginById($customer->getId());
            }
        }

        //Setting this one more time like control flag that we haves saved order
        //Must be checkout on success page to show it or not.
        $this->getCheckout()->setLastSuccessQuoteId($this->getQuote()->getId());

        $this->getQuote()->setIsActive(false);
        $this->getQuote()->save();

        return $this;
    }

    
}
