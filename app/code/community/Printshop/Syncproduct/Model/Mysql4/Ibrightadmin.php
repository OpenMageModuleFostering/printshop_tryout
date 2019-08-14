<?php
/** Class Printshop_Syncproduct_Model_Mysql4_Ibright
 * @category Local
 * @author Printshop
 */

class Printshop_Syncproduct_Model_Mysql4_Ibrightadmin extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the id refers to the key field in your database table.
        $this->_init('syncproduct/ibrightadmin', 'id');
    }
}