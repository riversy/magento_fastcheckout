<?php
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->run("

alter table `{$this->getTable('wp_ac_payment')}` 
   add column `base_total_amount` decimal(12,4) NULL after `payment_type`
");

$installer->endSetup();
