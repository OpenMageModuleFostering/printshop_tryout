<?php
/** Class Printshop_Syncproduct_Block_Adminhtml_Printshop_Producttype
 * @category    Local
 * @author Printshop
 */

class Printshop_Syncproduct_Block_Adminhtml_Printshop_Producttype extends Mage_Adminhtml_Block_Widget
{
  public function __construct()
  {
      parent::__construct();
     $this->setTemplate('doc/custom_options.phtml');
  }
	
	public function getQuestionOption($questionId)     
	{ 
		$sql = "SELECT * FROM docoption where questionid = '".$questionId."' ";
		$getData = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);
		return $getData;
	 }

    /*public function test123()
    {
		$getData = Mage::getModel('doc/docoption')->test();
		return $getData;
       // return $this->test();
    }*/

}