<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Riversy_Fastcheckout_Block_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
    protected $_totals = null;

    /**
     * @return Riversy_Fastcheckout_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('fastcheckout');
    }

    public function getTotals()
    {
        return $this->_totals = $this->_helper()->getTotals();
    }

    public function setTotals($value)
    {
        $this->_totals = $value;
        return $this;
    }

    protected function _formatPrice($value)
    {
        return Mage::helper('core')->currency($value, true, true);
    }

    public function getSubtotal()
    {
        $t = $this->getTotals();
        return $t['subtotal']->getValue();
    }

    public function getGrandTotal()
    {
        $t = $this->getTotals();
        return $t['grand_total']->getValue();
    }

    public function getSubtotalHtml()
    {
        return $this->_formatPrice($this->getSubtotal());
    }

    protected function _getTotalRenderer($code)
    {
        if (!isset($this->_totalRenderers[$code])) {

            $this->_totalRenderers[$code] = $this->_defaultRenderer;
            $config = Mage::getConfig()->getNode("global/sales/quote/totals/{$code}/renderer");
            if ($config) {
                $this->_totalRenderers[$code] = (string)$config;
            }
            $this->_totalRenderers[$code] = $this
                ->getLayout()
                ->createBlock(
                    $this->_totalRenderers[$code],
                    "{$code}_total_renderer"
                )
            ;
        }

        return $this->_totalRenderers[$code];
    }

    public function getGrandTotalHtml()
    {
        return $this->_formatPrice($this->getGrandTotal());

    }

    public function hasDiscount()
    {
        return !!$this->getDiscount();
    }

    public function getDiscount()
    {
        $t = $this->getTotals();
        if (isset($t['discount'])){
            return $t['discount']->getValue();
        } else {
            return false;
        }
    }

    public function getDiscountHtml()
    {
        return $this->_formatPrice($this->getDiscount());
    }

}