<?php
class WP_Advancedcheckout_PaymentController extends Mage_Core_Controller_Front_Action
{
    protected $_payment = null;
    
    /**
     *
     * @return Mage_A1pay_Model_A1paymodel
     */
    public function getA1pay() 
    {
        return Mage::getSingleton('a1pay/a1paymodel');
    }    
    
    protected function _getParam($key)
    {
        if ($p = $this->getRequest()->getParam('p')){
            try {
                $p = Zend_Json_Decoder::decode(base64_decode($p));
                return @$p[$key];                                
            } catch (Exception $e) {
                return false;                
            }
        }        
    }
    
    /**
     * @return WP_Advancedcheckout_Model_Payment
     */
    protected function _getPayment($order_id = null)
    {       
        if (!$this->_payment){       
            if ($order_id === null){
                $order_id = $this->_getParam('order_id');
            }
            
            if ($order_id){
                $payment = Mage::getModel('advancedcheckout/payment')->load($order_id, 'order_id');
                $this->_payment = $payment;
            }            
        }
        return $this->_payment;
    }
    
    /**
     *
     * @return WP_Advancedcheckout_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedcheckout');
    }
    
    public function getCheckout() 
    {
        return Mage::getSingleton('checkout/session');
    }    

    public function redirectAction()
    {
        $this->_getPayment()->addHistoryMessage($this->_helper()->__('Покупатель отправлен на сервис https://home.a1pay.ru/'));
        
        if ($order_id = $this->_getParam('order_id')){
            $this->getCheckout()->setLastOrderId($order_id);
        }
        
        if ($total = $this->_getParam('total')){
            $this->getCheckout()->setOrderTotal($total);
            $this->_getPayment()->setBaseTotalAmount($total)->save($this->_getParam('total'));
        }
                
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=utf8')
        ->setBody($this->getLayout()->createBlock('a1pay/a1paymodel_redirect')->toHtml());                        
    }
    
    
    protected function _redirectToCms($key)
    {
        $this->_redirect($key);
    }
    
    protected function _redirectToLastOrder()
    {
        if ($order_id =  $this->getCheckout()->getLastOrderId()){
            $this->_redirect('sales/order/view', array('order_id'=>$order_id));
        } else {
            $this->_redirect('sales/order/history');
        }              
    }


    public function successAction()
    {
        $payment = $this->_getPayment($this->getCheckout()->getLastOrderId());
        if ($payment){
            $payment->addHistoryMessage($this->_helper()->__('Покупатель успешно вернулся с сервиса A1Pay'));                        
        }
        
//        $this->_redirectToLastOrder();   
        $this->_redirectToCms('success');
    }
    
    public function failureAction()
    {
        $payment = $this->_getPayment($this->getCheckout()->getLastOrderId());
        if ($payment){
            $payment->addHistoryMessage($this->_helper()->__('Покупатель вернулся с сервиса A1Pay с ошибкой'));                        
        }
        
//        $this->_redirectToLastOrder();   
        $this->_redirectToCms('failure');
    }
    
    public function notificationAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->norouteAction();
            return;
        }
        $this->getA1pay()->processNotification($this->getRequest()->getPost());        
    }
    
    
}