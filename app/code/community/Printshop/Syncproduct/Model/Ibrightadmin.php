<?php
/** Class Printshop_Syncproduct_Model_Ibright
 * @category    Community
 * @author Printshop
 */

class Printshop_Syncproduct_Model_Ibrightadmin extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('syncproduct/ibrightadmin');
    }
}