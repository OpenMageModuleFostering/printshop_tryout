<?php
/** Class Printshop_Syncproduct_Block_Adminhtml_Syncproduct
 * @category    Local
 * @author Printshop
 */

class Printshop_Syncproduct_Block_Adminhtml_Syncproduct extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_syncproduct';
    $this->_blockGroup = 'syncproduct';
    $this->_headerText = Mage::helper('syncproduct')->__('Synchronize Product Data');

    //$this->_addButtonLabel = Mage::helper('syncproduct')->__('Go >> Import Product Data');
    parent::__construct();
    $this->_removeButton('add'); /*Add this line after calling parent constructor*/
  }
}