<?php
/** Class Printshop_Syncproduct_Model_Ibright
 * @category    Local
 * @author Printshop
 */

class Printshop_Syncproduct_Model_Ibrightfrontend extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('syncproduct/ibrightfrontend');
    }
}