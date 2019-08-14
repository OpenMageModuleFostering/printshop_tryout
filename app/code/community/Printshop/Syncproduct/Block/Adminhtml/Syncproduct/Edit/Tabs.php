<?php
/** Class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Edit_Tabs
 * @category    Local
 * @author Printshop
 */

class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('syncproduct_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('syncproduct')->__('Web2Print Synchronize'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
                'label'     => Mage::helper('syncproduct')->__('Synchronize'),
                'title'     => Mage::helper('syncproduct')->__('Synchronize'),
                'content'   => $this->getLayout()->createBlock('syncproduct/adminhtml_syncproduct_edit_tab_form')->toHtml(),
        ));
        $this->addTab('syncproduct_logs', array(
                'label'     => Mage::helper('syncproduct')->__('Synchronize Logs'),
                'title'     => Mage::helper('syncproduct')->__('Synchronize Logs'),
                'url'       => $this->getUrl('*/*/synclog', array('_current' => true)),
               // 'content'   => $this->getLayout()->createBlock('syncproduct/adminhtml_syncproduct_synclog')->toHtml(),
        ));
        $this->addTab('frontend_credentails', array(
                'label'     => Mage::helper('syncproduct')->__('Frontend Connection Setup'),
                'title'     => Mage::helper('syncproduct')->__('Frontend Connection Setup'),
                'url'       => $this->getUrl('*/*/frontendsetup', array('_current' => true)),
               // 'content'   => $this->getLayout()->createBlock('syncproduct/adminhtml_syncproduct_synclog')->toHtml(),
        ));
        $this->addTab('admin_setup', array(
                'label'     => Mage::helper('syncproduct')->__('Admin Connection Setup'),
                'title'     => Mage::helper('syncproduct')->__('Admin Connection Setup'),
                'url'       => $this->getUrl('*/*/adminsetup', array('_current' => true)),
        ));
		$this->addTab('guest_setup', array(
                'label'     => Mage::helper('syncproduct')->__('Guest Connection Setup'),
                'title'     => Mage::helper('syncproduct')->__('Guest Connection Setup'),
                'url'       => $this->getUrl('*/*/guestsetup', array('_current' => true))
        ));
        return parent::_beforeToHtml();
    }
}