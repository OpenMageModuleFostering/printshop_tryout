<?php
/** Class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Edit_Tab_Form
 * @category    Local
 * @author Printshop
 */

class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        //$fieldset = $form->addFieldset('syncproduct_form', array('legend'=>Mage::helper('syncproduct')->__('Magento-iBright'))); 
        if( Mage::getSingleton('adminhtml/session')->getsyncproductData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getsyncproductData());
            Mage::getSingleton('adminhtml/session')->setsyncproductData(null);
        }elseif( Mage::registry('syncproduct_data') ) {
            $form->setValues(Mage::registry('syncproduct_data')->getData());
        }
        return parent::_prepareForm();

    }

}