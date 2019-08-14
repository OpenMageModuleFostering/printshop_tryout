<?php
$installer = $this;
$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('syncproduct')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('syncproduct')}` (
`syncproduct_id` INT UNSIGNED NOT NULL auto_increment,
`template_name` varchar(255) NOT NULL default '',
 `magento_sku` varchar(255) NOT NULL default '--',
 `template_id` int(10) NOT NULL,
 `template_thumbnail` varchar(255) default NULL,
 `template_lockstatus` enum('0','1'),
 `created_at` timestamp,
 `updated_at` timestamp,
 PRIMARY KEY (`syncproduct_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$this->getTable('syncproduct_admin_ibright')}`;

CREATE TABLE IF NOT EXISTS {$this->getTable('syncproduct_admin_ibright')} (
`id` INT UNSIGNED NOT NULL auto_increment,
`ibright_url` varchar(255) default NULL,
`ibright_login` varchar(100) default NULL,
`ibright_password` varchar(100) default NULL,
`created_at` timestamp,
`updated_at` timestamp,
 PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `{$this->getTable('syncproduct_frontend_ibright')}`;

CREATE TABLE IF NOT EXISTS {$this->getTable('syncproduct_frontend_ibright')} (
`id` INT UNSIGNED NOT NULL auto_increment,
`ibright_frontend_url` varchar(255) default NULL,
`ibright_frontend_login` varchar(100) default NULL,
`ibright_frontend_password` varchar(100) default NULL,
`created_at` timestamp,
`updated_at` timestamp,
 PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

// Create print product as a product attribute

$setupPrint = new Mage_Eav_Model_Entity_Setup('core_setup');
$setupPrint->addAttribute('catalog_product', 'print_product', array(
        'label' => 'Print Product',
        'type' => 'int',
        'input' => 'select',
        'visible' => true,
        'required' => false,
        'backend'   => 'eav/entity_attribute_backend_array',
        'frontend'  => '',
        'source'   => 'syncproduct/source_option',
        'is_configurable' => 0,
        'position' => 1,
        'global'  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'option' => array (
                'value' => array('option1'=>array('Yes'),
                        'option2'=>array('No'),
                )
        ),
        'visible_on_front'  => false,
        'visible_in_advanced_search' => false,

));

// Create template id as a product attribute

$setupTplId = new Mage_Eav_Model_Entity_Setup('core_setup');

$setupTplId->addAttribute('catalog_product', 'template_id', array(
        'label' => 'Template ID',
        'type' => 'varchar',
        'input' => 'text',
        'visible' => true,
        'required' => false,
        'backend'   => '',
        'frontend'  => '',
        'source'   => '',
        'position' => 1,
        'visible_on_front'  => false,
        'visible_in_advanced_search' => false,

));

//$installer->getConnection()->addColumn($installer->getTable('user_template_design'), 'pdf_lowresurl', 'varchar(255) NULL DEFAULT NULL AFTER `pdf_url`');

/**
 * Product PDF - quote item
 **/

$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_item'), 'product_pdf', 'varchar(255) NULL DEFAULT NULL AFTER `sku`');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_item'), 'product_lowrespdf', 'varchar(255) NULL DEFAULT NULL AFTER `product_pdf`');
/**
 * Product Image - quote item
 **/

$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_item'), 'product_image', 'varchar(255) NULL DEFAULT NULL AFTER `name`');


/**
 * Product PDF - order item
 **/

$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_item'), 'product_pdf', 'varchar(255) NULL DEFAULT NULL AFTER `product_id`');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_item'), 'product_lowrespdf', 'varchar(255) NULL DEFAULT NULL AFTER `product_pdf`');

/**
 * Product Image - order item
 **/

$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_item'), 'product_image', 'varchar(255) NULL DEFAULT NULL AFTER `product_type`');

$installer->endSetup();


?>