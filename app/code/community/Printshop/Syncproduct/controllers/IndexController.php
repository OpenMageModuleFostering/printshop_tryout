<?php
/** Class Printshop_Syncproduct_IndexController
 * @category Local
 * @author Printshop
 */

class Printshop_Syncproduct_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction()
    {
     $this->loadLayout(array('default'));
     $this->renderLayout();
    }
}