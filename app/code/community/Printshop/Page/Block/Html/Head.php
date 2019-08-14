<?php
/** Class Printshop_Page_Block_Html_Head
 * This class override the functionality of Mage_Page_Block_Html_Head
 * @author Printshop
 */
class Printshop_Page_Block_Html_Head extends Mage_Page_Block_Html_Head
{
	protected function _construct()
    {
		$this->setTemplate('page/html/printshop_head.phtml');
    }

}

?>