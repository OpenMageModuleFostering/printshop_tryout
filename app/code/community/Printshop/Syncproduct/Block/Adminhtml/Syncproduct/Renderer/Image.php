<?php
/** Class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Renderer_Image
 * @category Community
 * @author Printshop
 */
class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    protected static $syncproductImgURL = null;   
    protected static $syncproductThumbWidth = null;
    public function __construct() {
        self::$syncproductImgURL = Mage::getBaseUrl('media').'syncproduct/images/thumbnail/';
        self::$syncproductThumbWidth = 100;
    }
    public function render(Varien_Object $row) {
        $imageFile = self::$syncproductImgURL.$row->getData($this->getColumn()->getIndex());
        list($width, $height, $type, $attr) = getimagesize($imageFile);
        $html = '<img ';
        $html .= 'id="' . $this->getColumn()->getId() . '" ';
        $html .= 'src="' . $imageFile . '"';
        $html .= 'width="'.$width.'" height="'.$height.'"';
        $html .= 'class="grid-image ' . $this->getColumn()->getInlineCss() . '"/>';
        return $html;
    }
}