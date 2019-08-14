<?php
$installer = $this;
$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('syncproduct_guest_ibright')}`;
 
CREATE TABLE `{$this->getTable('syncproduct_guest_ibright')}` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ibright_guest_url` varchar(255) default NULL,
  `ibright_guest_login` varchar(100) default NULL,
  `ibright_guest_password` varchar(100) default NULL,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `{$this->getTable('catalog_eav_attribute')}` cea 
INNER JOIN `{$this->getTable('eav_attribute')}` ea ON ea.attribute_id = cea.attribute_id
SET cea.used_in_product_listing = '1'
WHERE ea.attribute_code = 'print_product';

UPDATE `{$this->getTable('catalog_eav_attribute')}` cea 
INNER JOIN `{$this->getTable('eav_attribute')}` ea ON ea.attribute_id = cea.attribute_id
SET cea.used_in_product_listing = '1'
WHERE ea.attribute_code = 'template_id';

");
$installer->endSetup();
?>