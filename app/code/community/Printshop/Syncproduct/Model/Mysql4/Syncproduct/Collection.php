<?php
/** Class Printshop_Syncproduct_Model_Mysql4_Doc_Collection
 * @category Local
 * @author Printshop
 */

class Printshop_Syncproduct_Model_Mysql4_Syncproduct_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('syncproduct/syncproduct');
    }
}