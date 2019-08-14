<?php
/** Class Printshop_Syncproduct_Model_Mysql4_Syncproduct
 * @category Local
 * @author Printshop
 */

class Printshop_Syncproduct_Model_Mysql4_Syncproduct extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the syncproduct_id refers to the key field in your database table.
        $this->_init('syncproduct/syncproduct', 'syncproduct_id');
    }
    public function createNewAttributeSet($name) {
        Mage::app('default');
        $modelSet = Mage::getModel('eav/entity_attribute_set')
            ->setEntityTypeId(4) // 4 == "catalog/product"
            ->setAttributeSetName($name);
        $modelSet->save();
        return $modelSet->initFromSkeleton(4)->save(); // same thing
    }
}