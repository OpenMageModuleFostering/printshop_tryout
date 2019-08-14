<?php
/** Class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Edit
 * @category    Local
 * @author Printshop
 */

class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'syncproduct';
        $this->_controller = 'adminhtml_syncproduct';
        $this->_updateButton('save', 'label', Mage::helper('syncproduct')->__('Go >> Import Product Data'));
        $this->_removeButton('back');
        $this->_removeButton('reset');
    }
    public function getHeaderText() {
        if( Mage::registry('syncproduct_data') && Mage::registry('syncproduct_data')->getId() ) {
            return Mage::helper('syncproduct')->__("Edit Question '%s'", $this->htmlEscape(Mage::registry('syncproduct_data')->getTitle()));
        } else {
            return Mage::helper('syncproduct')->__('Go Import Data &gt;&gt;');
        }
    }
}