<?php
/** Class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Grid
 * @category    Local
 * @author Printshop
 */

class Printshop_Syncproduct_Block_Adminhtml_Syncproduct_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();

        $this->setId('syncproduct');
        $this->setDefaultSort('syncproduct_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
		//$this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('syncproduct/syncproduct')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns() {
        $this->addColumn('template_id', array(
                'header'    => Mage::helper('syncproduct')->__(''),
                'align'     => 'left',
                'width'     => '50px',
                'index'     => 'template_id',
                'type'      => 'checkbox'
        ));
        $this->addColumn('template_id', array(
                'header'    => Mage::helper('syncproduct')->__('Template ID'),
                'align'     => 'left',
                'width'     => '20px',
                'index'     => 'template_id',
        ));

        $this->addColumn('template_name', array(
                'header'    => Mage::helper('syncproduct')->__('Template Name'),
                'align'     => 'left',
                'width'     => '150px',
                'index'     => 'template_name',
        ));
        $this->addColumn('magento_sku', array(
                'header'    => Mage::helper('syncproduct')->__('Magento SKU'),
                'width'     => '20px',
                'align'     => 'left',
                'index'     => 'magento_sku',
        ));

        $this->addColumn('image', array(
                'header' => Mage::helper('syncproduct')->__('Thumbnail'),
                'align' => 'left',
                'width'     => '100px',
                'index' =>'template_thumbnail',
                'type' => 'image',
                'filter'    => false,
                'sortable'  => false,
                'escape'        => true,
                'renderer'  => 'syncproduct/adminhtml_syncproduct_renderer_image'

        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('template_id');
        $this->getMassactionBlock()->setFormFieldName('syncproduct');
        $this->getMassactionBlock()->addItem('delete', array(
                'label'    => Mage::helper('syncproduct')->__('Delete'),
                'url'      => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('syncproduct')->__('Are you sure?')
        ));

        $arrProductType = Mage::getSingleton('syncproduct/producttype')->getOptionArray();
        $arrProductType = Mage::getSingleton('syncproduct/producttype')->getOptionArray();

        array_unshift($arrProductType, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
                'label'=> Mage::helper('syncproduct')->__('Import in Magento'),
                'url'  => $this->getUrl('*/*/addToMagentoProduct', array('_current'=>true)),
                'additional' => array(
                        'visibility' => array(
                                'name' => 'productType',
                                'type' => 'select',
                                'class' => 'required-entry',
                                'label' => Mage::helper('syncproduct')->__('Product Type'),
                                'values' => $arrProductType
                        )
                )
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return;
        //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}