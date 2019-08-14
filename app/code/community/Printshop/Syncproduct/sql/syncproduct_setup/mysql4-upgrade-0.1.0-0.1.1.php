<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('user_template_design')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('user_template_design')}` (
`id` INT UNSIGNED NOT NULL auto_increment,
`user_id` varchar(100) NOT NULL default '',
 `product_id` int(11) NOT NULL,
 `pdf_url` varchar(255) default NULL,
 `pdf_lowresurl` varchar(255) default NULL,
 `image_url` varchar(255) default NULL,
 PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$installer->endSetup();
?>