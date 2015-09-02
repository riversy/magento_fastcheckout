<?php
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS `{$this->getTable('wp_ac_message')}`;
-- DROP TABLE IF EXISTS `{$this->getTable('wp_ac_payment')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('wp_ac_payment')}` (
  `payment_id` int(10) UNSIGNED NOT NULL auto_increment,
  `order_id` int(10) unsigned NOT NULL,
  `payment_type` varchar(100) not null,
  PRIMARY KEY (`payment_id`),
  KEY `FK_WP_AC_ORDER_ORDER` (`order_id`),
  CONSTRAINT `FK_WP_AC_ORDER_ORDER` FOREIGN KEY (`order_id`) REFERENCES `{$this->getTable('sales_flat_order')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('wp_ac_message')}` (
  `message_id` int(15) UNSIGNED NOT NULL auto_increment,
  `payment_id` int(10) unsigned NOT NULL,
  `message` tinytext NOT NULL,
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`message_id`),
  KEY `FK_WP_AC_MESSAGE_PAYMENT` (`payment_id`),
  CONSTRAINT `FK_WP_AC_MESSAGE_PAYMENT` FOREIGN KEY (`payment_id`) REFERENCES `{$this->getTable('wp_ac_payment')}` (`payment_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
