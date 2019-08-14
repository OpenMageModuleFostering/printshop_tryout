<?php
/** Class Printshop_Productpdf_Block_Adminhtml_Sales_Order_View_Tab_Productpdf
 *  This class will provide facility to download Order PDF files to admin
 *  @author Printshop
 */
class Printshop_Productpdf_Block_Adminhtml_Sales_Order_View_Tab_Productpdf extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate( 'productpdf/sales/order/view/tab/userpdf.phtml' );
    }

    public function getTabLabel()
    {
        return $this->__( 'Product Pdf' );
    }

    public function getTabTitle()
    {
        return $this->__( 'Product Pdf' );
    }

    public function getTabClass()
    {
        return '';
    }

    public function getClass()
    {
        return $this->getTabClass();
    }

    public function getRowUrl()
    {
        return $this->getUrl('*/sales_order/productpdf', array('_current' => true));
    }
     public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function getOrder()
    {
        return Mage::registry( 'current_order' );
    }
    
}

   