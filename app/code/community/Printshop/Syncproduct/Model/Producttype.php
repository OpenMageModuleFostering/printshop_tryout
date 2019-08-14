<?php
/** Class Printshop_Syncproduct_Model_Status
 * @category Local
 * @author Printshop
 */

class Printshop_Syncproduct_Model_Producttype extends Varien_Object {
    const PRODUCT_PRINT     = "Print Product";
    const PRODUCT_STANDARD  = "Standard Product";

    static public function getOptionArray() {
        return array(
                self::PRODUCT_PRINT    => Mage::helper('syncproduct')->__('Print Product'),
                self::PRODUCT_STANDARD => Mage::helper('syncproduct')->__('Standard Product')
        );
    }
}