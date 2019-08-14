<?php
/** Class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Edit_Form
 * @category    Local
 * @author Printshop
 */

class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
                        'id' => 'edit_form',
                        'action' => $this->getUrl('*/*/importproduct', array('id' => $this->getRequest()->getParam('id'))),
                        'method' => 'post'
                )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}