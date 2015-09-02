<?php
/**
 * @author Igor Goltsov <riversy@gmail.com>
 */

class WP_Advancedcheckout_Adminhtml_PaymentController extends Mage_Adminhtml_Controller_Action
{
    
    protected $_payment = null;
    /**
     *
     * @return WP_Advancedcheckout_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedcheckout');
    }
    
    /**
     * Notitier
     * 
     * @return WP_Advancedcheckout_Helper_Notify
     */
    protected function _notify()
    {
        return Mage::helper('advancedcheckout/notify');
    }


    /**
     * Response for Ajax Request
     *  @param array $result
     */
    protected function _ajaxResponse($result = array())
    {
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }    
    
    protected function _getHistoryHtml()
    {
        $block = $this->getLayout()->createBlock('advancedcheckout/adminhtml_sales_order_history');
        if ($block){
            $block->setOrderId($this->getRequest()->getParam('order_id'));
            return $block->toHtml();
        }
    }
    
    /**
     * Current Order
     * @return Mage_Sales_Model_Order|boolean
     */
    protected function _getOrder()
    {
        if ($orderId = $this->getRequest()->getParam('order_id')){
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()){
                return $order;
            }            
        }
        return false;
    }
    
    /**
     *
     * @param string|integer $order_id
     * @return WP_Advancedcheckout_Model_Payment
     */
    protected function _getPayment($order_id = null)
    {       
        if (!$this->_payment){                  
            if ($order_id){
                $payment = Mage::getModel('advancedcheckout/payment')->load($order_id, 'order_id');
                $this->_payment = $payment;
            }            
        }
        return $this->_payment;
    }    
    
    
    public function sendemailAction()
    {
        try {
            $this->_notify()->sendEmailToCustomer();
            
            if ($this->getRequest()->getParam('copy_to')){
                $this->_notify()->sendEmailToAdmin();
            }
                     
            if ($order = $this->_getOrder()){
                
                $reqTotal = $this->getRequest()->getParam('total');
                $total =  $reqTotal ? $reqTotal : $order->getBaseGrandTotal();
                $payment = $this->_getPayment($order->getId());
                $payment                    
                    ->setBaseTotalAmount($total)
                    ->save();                
                
                $total = Mage::app()->getStore()->formatPrice($total);
                
                if ($order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT){
                    $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true)->save();
                }
                
                $order->addStatusToHistory(
                     Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                     $this->_helper()->__('Выставлен счет на %s', $total),
                     $notified = false
                );      
                
                $payment->addHistoryMessage($this->_helper()->__('Выставлен счет на %s', $total)); 
            }                        
            
            $content = $this->_getHistoryHtml();                        
            $this->_ajaxResponse(array('success'=> true, 'content'=>$content));
        } catch (Exception $e) {
            $this->_ajaxResponse(array('error'=> true, 'message'=>$e->getMessage()));
        }                
    }
    
    public function historyAction()
    {
        $content = $this->_getHistoryHtml();
        $this->_ajaxResponse(array('success'=> true, 'content'=>$content));
    }
    
}