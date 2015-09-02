<?php
class WP_Advancedcheckout_Helper_Notify extends Mage_Core_Helper_Abstract
{
    protected $_order = null;
    
    /**
     * @return WP_Advancedcheckout_Model_Payment
     */
    public function getPayment()
    {
        return Mage::getModel('advancedcheckout/payment')->load($this->getOrder()->getId(), 'order_id');
    }
    
    /**
     * Retrives order instance
     * @return Mage_Sales_Model_Order
     */
    protected function getOrder()
    {
        if (!$this->_order){
            $this->_order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
            
            $p = array(
                'order_id'=>$this->_order->getId(),
                'total' => Mage::app()->getRequest()->getParam('total'),                
            );
            
            $p = base64_encode( Zend_Json_Encoder::encode( $p ) );
            
            $url = Mage::getModel('core/url')->getUrl('advancedcheckout/payment/redirect', 
                                                                              array(
                                                                                    '_secure'=>true,
                                                                                    'p' => $p
                                                                              ));
            $url = str_replace("advancedcheckout_admin", "advancedcheckout", $url);
            $this->_order->setPaymentLink($url);
            $this->_order->setRequestedTotal(Mage::app()->getStore()->formatPrice(Mage::app()->getRequest()->getParam('total'), true));
        }
        return $this->_order;
    }
    
    public function sendEmailTo($recipient)
    {
        $sender = Mage::getStoreConfig('advancedcheckout/payment/sender');
                        
        if ($recipient && $sender){
            
            try {
            
                $email = Mage::getModel('core/email_template');
                $email->setDesignConfig(array('area'=>'frontend', 'store'=>Mage::app()->getStore()->getId()))
                        ->sendTransactional(
                            Mage::getStoreConfig('advancedcheckout/payment/email_template'),
                            $sender,
                            $recipient,
                            '',
                            array(
                                'order'=>$this->getOrder()
                            )
                        );

                $this->getPayment()->addHistoryMessage($this->__('Email with link sended to %s', $recipient));
                
            } catch (Exception $e) {
                $this->getPayment()->addHistoryMessage($this->__('Email sending failed! (%s)', $e->getMessage()));
            }

        }               
        return $this;
    }
    
    public function sendInvoiceEmailToAdmin($orderId, $amount)
    {
        $emails = Mage::getStoreConfig('advancedcheckout/payment/receiver');        
        $emails = explode(",", $emails);        
        foreach ($emails as $email){
            $this->sendInvoiceEmail(trim($email), $orderId, $amount);            
        }                
        return $this;
    }
    
    public function sendInvoiceEmail($recipient, $orderId, $amount)
    {
        $sender = Mage::getStoreConfig('advancedcheckout/payment/sender');
                        
        if ($recipient && $sender && $orderId){
            
            $order = Mage::getModel('sales/order')->load($orderId);
            try {
                
                $email = Mage::getModel('core/email_template');
                $email->setDesignConfig(array('area'=>'frontend', 'store'=>Mage::app()->getStore()->getId()))
                        ->sendTransactional(
                            Mage::getStoreConfig('advancedcheckout/payment/invoice_email_template'),
                            $sender,
                            $recipient,
                            '',
                            array(
                                'order'=>$order,
                                'amount' => $amount
                            )
                        );

                $this->getPayment()->addHistoryMessage($this->__('Оповещение об оплате отослано на %s', $recipient));
                
            } catch (Exception $e) {
                $this->getPayment()->addHistoryMessage($this->__('Ошибка при отсылке оповещения об оплате! (%s)', $e->getMessage()));
            }

        }               
        return $this;        
    }
    
    public function sendEmailToCustomer()
    {
        $email = $this->getOrder()->getCustomerEmail();
        $this->sendEmailTo($email);        
        return $this;
    }
    
    public function sendEmailToAdmin()
    {
        $emails = Mage::getStoreConfig('advancedcheckout/payment/receiver');        
        $emails = explode(",", $emails);        
        foreach ($emails as $email){
            $this->sendEmailTo(trim($email));            
        }                
        return $this;
    }

    
}
