<?php
/**
 * Product controller
 * @category   Printshop
 * @package    Printshop_Catalog
 */

require_once "Mage/Catalog/controllers/ProductController.php";
class Printshop_Catalog_ProductController extends Mage_Catalog_ProductController {
    /**
     * Product save PDF and image preview..
     */
    public function viewAction() {
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        // Prepare helper and params
        $viewHelper = Mage::helper('catalog/product_view');

        $params = new Varien_Object();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    $this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
        // check if pdf url is set..
        if($this->getRequest()->getParam('pdfUrl')) {
            $objIbright = Mage::getModel('syncproduct/ibrightfrontend');
            $getIbrightDetails = $objIbright->load('1');
            $frontendUrl = $getIbrightDetails->getIbrightFrontendUrl();
            $getFrontendUrl = parse_url($frontendUrl);
            $refUrl = $_SERVER['HTTP_REFERER'];
            $getReferer = parse_url($refUrl);
            // check that if referee are coming from ibright server..
            if($getFrontendUrl['host'] == $getReferer['host']) {

                $objCatlogModel = Mage::getModel('syncproduct/catalog');


                // check if user is logged in or not..
                if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
                    $data['user_id'] = $customerId;
                }
                else {
                    $customerId = session_id();
                    $data['user_id'] = $customerId;
                }

                // if same user is making changes to same template then remove previous design..
                $checkTemplateData = Mage::helper('syncproduct')->customTemplateId($productId, $customerId);
                if(!empty($checkTemplateData)) {
					$checkProduct  = $objCatlogModel->load($checkTemplateData);
					$checkProduct->delete();
                }
                $data['product_id'] = $productId;
                $data['pdf_url'] = utf8_decode($this->getRequest()->getParam('pdfUrl'));
                $data['pdf_lowresurl'] = utf8_decode($this->getRequest()->getParam('pdfLowUrl'));
                $data['image_url'] = utf8_decode($this->getRequest()->getParam('thumbUrl'));
                $objCatlogModel->setData($data);
                $objCatlogModel->save();
            }
        }
    }

    /**
     * Display product image action
     *
     * @deprecated
     */
    public function imageAction() {
        $size = (string) $this->getRequest()->getParam('size');
        if ($size) {
            $imageFile = preg_replace("#.*/catalog/product/image/size/[0-9]*x[0-9]*#", '',
                    $this->getRequest()->getRequestUri());
        } else {
            $imageFile = preg_replace("#.*/catalog/product/image#", '',
                    $this->getRequest()->getRequestUri());
        }

        if (!strstr($imageFile, '.')) {
            $this->_forward('noRoute');
            return;
        }

        try {
            $imageModel = Mage::getModel('catalog/product_image');
            $imageModel->setSize($size)
                    ->setBaseFile($imageFile)
                    /**
                     * Resizing has been commented because this one method are deprecated
                     */
                    //->resize()
                    ->setWatermark( Mage::getStoreConfig('catalog/watermark/image') )
                    ->saveFile()
                    ->push();
        } catch( Exception $e ) {
            $this->_forward('noRoute');
        }
    }
}
