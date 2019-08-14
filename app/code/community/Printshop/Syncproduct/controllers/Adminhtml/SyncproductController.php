<?php
/** Class Printshop_Syncproduct_Adminhtml_DocController
 * @category Local
 * @author Printshop
 */
class Printshop_Syncproduct_Adminhtml_SyncproductController extends Mage_Adminhtml_Controller_action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('System')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('adminhtml')->__('Web2Print'));
        return $this;
    }
    public function indexAction() {
        // $this->_forward('edit');
        $this->_initAction()
                ->renderLayout();
    }
    public function editAction() {
        $this->loadLayout();
        $this->_setActiveMenu('system/syncproduct');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Question Manager'), Mage::helper('adminhtml')->__('Question Manager'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->setTemplate('syncproduct/edit.phtml');
        $this->renderLayout();
    }
    public function synclogAction() {
        $this->loadLayout();
        $this->_setActiveMenu('system/syncproduct');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Synchronize Log'), Mage::helper('adminhtml')->__('Synchronize Log'));
        $this->renderLayout();
    }
    // function to import templates from iBright server..
    public function importproductAction() {
        if ($this->getRequest()->getPost()) {
            $formData = $this->getRequest()->getPost();

            $objIbright = Mage::getModel('syncproduct/ibrightadmin');
            $arrCollection = $objIbright->load('1');    // check if any url is already exists..

            $ibrightUrl    = $arrCollection['ibright_url'];
            $ibrightUser  = $arrCollection['ibright_login'];
            $ibrightPwd   = base64_decode($arrCollection['ibright_password']);
            if($ibrightUrl == '') {
                $this->_getSession()->addError("Please create connection setup and then import data");
            }
            else {
                // Use CURL to connect and get data from iBright
                $ch = curl_init();
                // set URL and other appropriate options
                curl_setopt($ch, CURLOPT_URL, $ibrightUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                $postData = "login=".$ibrightUser."&password=".$ibrightPwd;
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                $getTemplateXml = curl_exec($ch);
                //$errSync = curl_error($ch);
                curl_close($ch);

                if(!strpos($getTemplateXml, "template")) {
                    $stripHtmlErrMsg = trim(str_replace("\n", " ", strip_tags($getTemplateXml)));
                    $errMsg = preg_replace("'\s+'", ' ', $stripHtmlErrMsg);
                    $syncErrorMsg = "CONFIG". "\t". $_SERVER['REMOTE_ADDR']. "\t". $errMsg. "\t". date("d-m-Y h:i:s");
                    // create log for error..
                    $logFile = Mage::getBaseDir('base'). DS . 'var'. DS . 'synclog'. DS . 'log.txt';
                    if(file_exists($logFile)) {
                        if(file_get_contents($logFile) != "") {
                            $getLogFileData = file_get_contents($logFile);
                            chmod($logFile, 0777);
                            file_put_contents($logFile, $getLogFileData."\r\n".$syncErrorMsg);
                        }
                        else {
                            chmod($logFile, 0777);
                            file_put_contents($logFile, $syncErrorMsg);
                        }
                    }
                    else {
                        fopen($ourFileName, 'w');
                        chmod($logFile, 0777);
                        file_put_contents($logFile, $syncErrorMsg);
                    }
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('syncproduct')->__($errMsg));
                }
                else {
                    try {
                        $xmlName     = time().".xml";
                        $syncDirPath = Mage::getBaseDir('base').DS.'media'.DS.'syncproduct'.DS;
                        if(!file_exists($syncDirPath)) {
                            mkdir($syncDirPath, 0777);
                        }
                        $syncXmlPath = $syncDirPath.'xml'.DS;
                        if(!file_exists($syncXmlPath)) {
                            mkdir($syncXmlPath, 0777);
                        }
                        $templateXml = $syncXmlPath.$xmlName;

                        // write XML in a file before parsing..

                        $fh = fopen($templateXml, 'w') or die("can't open file");
                        fwrite($fh, $getTemplateXml);
                        fclose($fh);
                        chmod($templateXml, 0777);
                        if(file_exists($templateXml)) {
                            $xmlObj = new Varien_Simplexml_Config($templateXml);
                            $xmlData = $xmlObj->getNode();
                            $model = Mage::getModel('syncproduct/syncproduct');
                            $data = array();
                            $i = 0;
                            foreach($xmlData as $tplData) {
                                // check if template is already available then remove and import this again..
                                $tplCollection = $model->getCollection()
                                        ->addFieldToFilter('template_id', $tplData['id'])
                                        ->load();
                                if(!empty($tplCollection)) {
                                    foreach($tplCollection as $valTemplate) {
                                        $tplId = $valTemplate->getSyncproductId();
                                        if($tplId !=  '') {
                                            $tplDataCollection = $model->load($tplId);
                                            $tplDataCollection->delete();
                                        }
                                    }
                                }
                                // checking and import again ends here..
                                $data['template_id'] = $tplData['id'];
                                $data['template_name'] = $tplData['name'];
                                $data['template_lockstatus'] = $tplData['lock_status'];
                                // get product thumnail path..
                                $cImg = curl_init();
                                // set URL and other appropriate options
                                $imgUrl = str_replace("getAllTemplatesXML.html", "getPreviewImage.html", $ibrightUrl);
                                curl_setopt($cImg, CURLOPT_URL, $imgUrl);
                                curl_setopt($cImg, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($cImg, CURLOPT_HEADER, 0);
                                curl_setopt($cImg, CURLOPT_POST, 1);
                                $imgPostData = "login=".$ibrightUser."&password=".$ibrightPwd."&template=".$data['template_id']."&res=high&page=1";
                                curl_setopt($cImg, CURLOPT_POSTFIELDS, $imgPostData);
                                $getTemplateImage = curl_exec($cImg);
                                $err = curl_error($cImg);
                                curl_close($cImg);
                                $templateImageName   = $tplData['id'].".jpg";
                                $templateImagePath   = $syncDirPath.'images'.DS;
                                if(!file_exists($templateImagePath)) {
                                    mkdir($templateImagePath, 0777);
                                }
                                $templateImage = $templateImagePath.$templateImageName;

                                // save images in a file..

                                $fh = fopen($templateImage, 'w') or die("can't open file");
                                fwrite($fh, $getTemplateImage);
                                fclose($fh);
                                if(file_exists($templateImage)) {
                                    chmod($templateImage, 0777);
                                    $data['template_thumbnail'] = $templateImageName;
                                }
                                // Now work for image thumbnail..
                                $imageUrl = Mage::getBaseUrl('media'). "syncproduct". DS .$templateImageName;
                                // create folder
                                if(!file_exists($templateImagePath. "thumbnail". DS))
                                    mkdir($templateImagePath. "thumbnail". DS, 0777);
                                $imageResized = $templateImagePath. "thumbnail". DS . $templateImageName;
                                // thumbnail image path..
                                $dirImg = $templateImagePath. $templateImageName;
                                // save the thumbnail image to the thumbnail directory

                                $imageObj = new Varien_Image($dirImg);
                                $imageObj->constrainOnly(TRUE);
                                $imageObj->keepAspectRatio(TRUE);
                                $imageObj->keepFrame(FALSE);
                                $imageObj->resize(120, 120);
                                $imageObj->save($imageResized);
                                // Image thumnail creation ENDS Here. Save all the data..

                                // check if template is already exists in Magento as a product..
                                $objChekMagentoDatabse = Mage::getResourceModel('catalog/product_collection');
                                $productCollection = array();
                                $data['magento_sku'] = "--";
                                $productCollection = $objChekMagentoDatabse->addFieldToFilter('template_id', $data['template_id'])
                                        ->addAttributeToSelect('sku')
                                        ->load();
                                if(!empty($productCollection)) {
                                    foreach($productCollection as $valProduct) {
                                        $data['magento_sku'] = $valProduct->getSku();
                                    }
                                }
                                $model->setData($data);
                                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                                    $model->setCreatedTime(now())
                                            ->setUpdateTime(now());
                                } else {
                                    $model->setUpdateTime(now());
                                }
                                $i++;
                                $model->save();
                            }
                            // unlink imported template XML..
                            chmod($templateXml, 0777);
                            unlink($templateXml);
                        }
                        $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully added', $i));
                    }
                    catch (Exception $e) {
                        //$this->_getSession()->addError($e->getMessage());
                        // create log for error..
                        $logMsg = "CONFIG". "\t". $_SERVER['REMOTE_ADDR']. "\t". $e->getMessage(). "\t". date("d-m-Y h:i:s");
                        $logFile = Mage::getBaseDir('base'). DS . 'var'. DS . 'synclog'. DS . 'log.txt';
                        if(file_exists($logFile)) {
                            if(file_get_contents($logFile) != "") {
                                $getLogFileData = file_get_contents($logFile);
                                chmod($logFile, 0777);
                                file_put_contents($logFile, $getLogFileData."\r\n".$logMsg);
                            }
                            else {
                                chmod($logFile, 0777);
                                file_put_contents($logFile, $logMsg);
                            }
                        }
                        else {
                            fopen($ourFileName, 'w');
                            chmod($logFile, 0777);
                            file_put_contents($logFile, $logMsg);
                        }
                    }
                }
            }
        }
		 // clean magento cache..
		Mage::app()->getCache()->clean();
		// rebuild index..
		Mage::getResourceModel('catalog/product_flat_indexer')->rebuild();
        $this->_redirect('*/*/');
    }
    // function for add product in Magento database the attribute set will be taken from the options selected by the admin..
    public function addToMagentoProductAction() {
        $arrProductType = $this->getRequest()->getParam('syncproduct');
        if(!is_array($arrProductType)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($arrProductType as $syncProductId) {
                    // create product in Magento..
                    $objSyncProduct =  Mage::getModel('syncproduct/syncproduct');
                    $arrNewProduct  = $objSyncProduct->load($syncProductId);

                    // if template is already added as a product in magento then update its preview image only..

                    if( $arrNewProduct['magento_sku'] != "--") {
                        // update images in magento product..
                        $objChekMagentoDatabse = Mage::getResourceModel('catalog/product_collection');
                        $productCollection = array();
                        $productCollection = $objChekMagentoDatabse->addFieldToFilter('template_id', $arrNewProduct['template_id'])
                                ->addAttributeToSelect('*')
                                ->load();
                        if(!empty($productCollection)) {
                            foreach($productCollection as $valProduct) {
                                $magentoImgPath = Mage::getBaseDir('base').DS.'media'. DS . 'catalog'. DS . 'product' . DS;
                                // unlink product image which are already exists..
                                if(file_exists($magentoImgPath.$valProduct->getImage()))
                                    unlink($magentoImgPath.$valProduct->getImage());
                                if(file_exists($magentoImgPath.$valProduct->getSmallImage()))
                                    unlink($magentoImgPath.$valProduct->getSmallImage());
                                if(file_exists($magentoImgPath.$valProduct->getThumbnail()))
                                    unlink($magentoImgPath.$valProduct->getThumbnail());

                                $imagePath = Mage::getBaseDir('base').DS.'media'.DS.'syncproduct'.DS.  'images'. DS;
                                $imageName = $imagePath.$arrNewProduct['template_thumbnail'];

                                $valProduct->addImageToMediaGallery($imageName , array ('image', 'small_image','thumbnail'), false, false);
                                $valProduct->save();
                                unlink($imageName);
                                unlink($imagePath.'thumbnail'. DS . $arrNewProduct['template_thumbnail']);
                                $arrNewProduct->delete();
                               
                            }
                        }
                    }else {
                        //$newProduct    = Mage::getModel('catalog/product');
                        $newProduct    = new Mage_Catalog_Model_Product();
                        $newProduct->setWebsiteIds(array(1));
                        $newProduct->setSku('ib-'.$arrNewProduct['template_id'].$arrNewProduct['syncproduct_id']);
                        $newProduct->setTemplateId($arrNewProduct['template_id']);

                        //$newProduct->setCategoryIds(array(1152));
                        
                        // get Attribute set ID for Default..
                        $entityTypeId = Mage::getModel('eav/entity')
                                ->setType('catalog_product')
                                ->getTypeId();
                        $attributeSetName   = 'Default';
                        $attributeSetId     = Mage::getModel('eav/entity_attribute_set')
                                ->getCollection()
                                ->setEntityTypeFilter($entityTypeId)
                                ->addFieldToFilter('attribute_set_name', $attributeSetName)
                                ->getFirstItem()
                                ->getAttributeSetId();

                        $newProduct->setAttributeSetId($attributeSetId);
                        $newProduct->setTypeId('simple');
                        // get attribute value from attribute code..
                        $attrb = Mage::getModel('catalog/product')->getResource()->getAttribute("print_product");
                        if($attrb->usesSource()) {
                            $options = $attrb->getSource()->getAllOptions(false);
                        }
                        // check in which product type the product is going to add in Magento..
                        if($this->getRequest()->getParam('productType') == "Print Product")
                            $newProduct->setPrintProduct($options[1]['value']);
                        elseif($this->getRequest()->getParam('productType') == "Standard Product")
                            $newProduct->setPrintProduct($options[0]['value']);

                        $newProduct->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
                        $newProduct->setName($arrNewProduct['template_name']);
                        $newProduct->setDescription($arrNewProduct['template_name']);
                        $newProduct->setShortDescription($arrNewProduct['template_name']);
                        $newProduct->setStatus(2);
                        $newProduct->setTaxClassId(0);
                        $newProduct->setWeight(1.0000);
                        $newProduct->setPrice(1.00);
                        $newProduct->setStockData(array(
                                'is_in_stock' => 1,
                                'qty' => 10
                        ));
                        $newProduct->setCreatedAt(strtotime('now'));
                        // copy images in magento product..
                        $imageName = Mage::getBaseDir('base').DS.'media'.DS.'syncproduct'.DS.  'images'. DS . $arrNewProduct['template_thumbnail'];
                        $newProduct->addImageToMediaGallery($imageName , array ('image', 'small_image','thumbnail'), false, false);
                        // call save() method to save your product with updated data
                        $newProduct->save();
                        $pdtId = $newProduct->getId();
                        // if print type product then create product options..
                        if($this->getRequest()->getParam('productType') == "Print Product")
                            $this->createCustomOptions($pdtId);

                        //unlink images..
                        unlink($imageName);
                        unlink(Mage::getBaseDir('base').DS.'media'.DS.'syncproduct'.DS.  'images'. DS .'thumbnail'. DS . $arrNewProduct['template_thumbnail']);
                        // remove data from sync table..
                        $arrNewProduct->delete();
                    }
                }
                // clean magento cache..
                Mage::app()->getCache()->clean();
                // rebuild index..
                Mage::getResourceModel('catalog/product_flat_indexer')->rebuild();
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully added as product. Please do "FlushCatalog Images Cache" in Magento to see all the new previews', count($arrProductType)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    // function to delete templates..
    public function massDeleteAction() {
        $syncIds = $this->getRequest()->getParam('syncproduct');
        if(!is_array($syncIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($syncIds as $syncId) {
                    $objTpl = Mage::getModel('syncproduct/syncproduct')->load($syncId);
                    $templateId = $objTpl->getTemplateId();
                    $tplImgPath = Mage::getBaseDir('base').DS.'media'.DS.'syncproduct'.DS;
                    //unlink images..
                    chmod($tplImgPath . "images". DS . $templateId.".jpg", 0777);
                    unlink($tplImgPath . "images". DS . $templateId.".jpg");
                    //unlink thumb images..
                    chmod($tplImgPath . "images". DS . "thumbnail". DS . $templateId.".jpg", 0777);
                    unlink($tplImgPath . "images". DS . "thumbnail". DS . $templateId.".jpg");
                    $objTpl->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($syncIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    // create product custom options when added as a product in Magento database..
    public function createCustomOptions($productId) {
        $product = Mage::getModel('catalog/product')->load($productId);
        $optionData1 =
                array(
                'is_delete'         => 0,
                'is_require'        => true,
                'previous_group'    => '',
                'title'             => 'Druck ',
                'type'              => 'radio',
                'price_type'        => 'fixed',
                'price'             => 0,
                'sort_order'        => 1,
                'values'            => array(
                        array(
                                'is_delete'     => 0,
                                'title'         => 'Digital(Digitaldruck Xerox)',
                                'price_type'    => 'fixed',
                                'price'         => 0,
                        //'sku'           => 'product sku',
                        //'option_type_id'=> -1,
                        ),
                        array(
                                'is_delete'     => 0,
                                'title'         => 'Standard (Offsetdruck)',
                                'price_type'    => 'fixed',
                                'price'         => 0,
                )));

        $optionData2 = array(
                'is_delete'         => 0,
                'is_require'        => true,
                'previous_group'    => '',
                'title'             => 'Papier ',
                'type'              => 'radio',
                'price_type'        => 'fixed',
                'price'             =>  0,
                'sort_order'        => 2,
                'values'            => array(
                        array(
                                'is_delete'     => 0,
                                'title'         => 'Standard (weiss, gestrichen, matt)',
                                'price_type'    => 'fixed',
                                'price'         => 0,
                        //'sku'           => 'product sku',
                        //'option_type_id'=> -1,
                        ),
                        array(
                                'is_delete'     => 0,
                                'title'         => 'Natur (weiss, ungestrichen, matt)',
                                'price_type'    => 'fixed',
                                'price'         => 0,
                        ),
                        array(
                                'is_delete'     => 0,
                                'title'         => 'Deluxe (weiss, gestrichen, satin)',
                                'price_type'    => 'fixed',
                                'price'         => '0.20',
                )));

        $optionData3 = array(
                'is_delete'         => 0,
                'is_require'        => true,
                'previous_group'    => '',
                'title'             => 'Wunschtermin ',
                'type'              => 'radio',
                'price_type'        => 'fixed',
                'price'             =>  0,
                'sort_order'        => 3,
                'values'            => array(
                        array(
                                'is_delete'     => 0,
                                'title'         => 'Normal',
                                'price_type'    => 'fixed',
                                'price'         =>  0,
                        //'sku'           => 'product sku',
                        //'option_type_id'=> -1,
                        ),
                        array(
                                'is_delete'     => 0,
                                'title'         => 'Express',
                                'price_type'    => 'fixed',
                                'price'         => '15.00',
                )));


        $product->setProductOptions(array($optionData1, $optionData2, $optionData3));
        $product->setCanSaveCustomOptions(true);
        $product->save();
        // unset product options so that it can not make duplicate entries..
        Mage::getSingleton('catalog/product_option')->unsetOptions();
        return;
    }
    // function to setup frontend connection setup..
    public function frontendsetupAction() {
		$this->loadLayout();
        $this->_setActiveMenu('system/syncproduct');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Frontend Connection Setup'), Mage::helper('adminhtml')->__('Frontend Connection Setup'));
		
		$objIbright = Mage::getModel('syncproduct/ibrightfrontend');
		$arrCollection = $objIbright->load('1');    // check if any url is already exists..
		Mage::register('frontData', $arrCollection);
		
        $this->renderLayout();
        if($this->getRequest()->getPost()) {
            $arrPostedData = $this->getRequest()->getPost();
            $dataUrl =  $arrCollection['ibright_frontend_url'];
            $data['ibright_frontend_url']     = $arrPostedData['ibrightfrnturl'];
            $data['ibright_frontend_login']   = $arrPostedData['ibrightfrntuser'];
            $data['ibright_frontend_password']= base64_encode($arrPostedData['ibrightfrntpassword']);
            $objIbright->setData($data);
            if ($objIbright->getCreatedTime == NULL || $objIbright->getUpdateTime() == NULL) {
                $objIbright->setCreatedTime(now())
                        ->setUpdateTime(now());
            } else {
                $objIbright->setUpdateTime(now());
            }

            if($dataUrl == '')
                $objIbright->save();
            else
                $objIbright->setId(1)->save();
            $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully added', 1));
            $this->_redirect('*/*/frontendsetup');
        }
    }
    // function to save admin setup connections..
    public function adminsetupAction() {
        $this->loadLayout();
        $this->_setActiveMenu('system/syncproduct');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Admin Connection Setup'), Mage::helper('adminhtml')->__('Admin Connection Setup'));
		/**********************************************/
		
		$objIbright = Mage::getModel('syncproduct/ibrightadmin');
		$arrCollection = $objIbright->load('1');    // check if any url is already exists..
		Mage::register('adminData', $arrCollection);
		
		/**********************************************/
        $this->renderLayout();
        if($this->getRequest()->getPost()) {
            $arrPostedData = $this->getRequest()->getPost();
            
            $dataUrl =  $arrCollection['ibright_url'];
            $dataCredentials['ibright_url']     = $arrPostedData['ibrighturl'];
            $dataCredentials['ibright_login']   = $arrPostedData['ibrightuser'];
            $dataCredentials['ibright_password']= base64_encode($arrPostedData['ibrightpassword']);
            $a = $objIbright->setData($dataCredentials);
			//echo "<pre>"; print_r($a); exit;
            if ($objIbright->getCreatedTime == NULL || $objIbright->getUpdateTime() == NULL) {
                $objIbright->setCreatedTime(now())
                        ->setUpdateTime(now());
            } else {
                $objIbright->setUpdateTime(now());
            }
            if($dataUrl == '')
                $objIbright->save();
            else
                $objIbright->setId(1)->save();

            $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully added', 1));
            $this->_redirect('*/*/adminsetup');
        }
    }
	// function to save guest user account setup connections..
    public function guestsetupAction() {
		$this->loadLayout();
		$this->_setActiveMenu('system/syncproduct');
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Guest Connection Setup'), Mage::helper('adminhtml')->__('Guest Connection Setup'));
		$objIbright = Mage::getModel('syncproduct/ibrightguest');
		$arrCollection = $objIbright->load('1');    // check if any url is already exists..
		Mage::register('guestData', $arrCollection);
        $this->renderLayout();
        
		if($this->getRequest()->getPost()) {
            $arrPostedData = $this->getRequest()->getPost();
			$dataUrl =  $arrCollection['ibright_guest_url'];
            $dataCredentials['ibright_guest_url']     = $arrPostedData['ibrightguesturl'];
            $dataCredentials['ibright_guest_login']   = $arrPostedData['ibrightguestuser'];
            $dataCredentials['ibright_guest_password']= base64_encode($arrPostedData['ibrightguestpassword']);
            
			$objIbright->setData($dataCredentials);
            if ($objIbright->getCreatedTime == NULL || $objIbright->getUpdateTime() == NULL) {
                $objIbright->setCreatedTime(now())
                        ->setUpdateTime(now());
            } else {
                $objIbright->setUpdateTime(now());
            }
            if($dataUrl == '')
                $objIbright->save();
            else
                $objIbright->setId(1)->save();				
			$this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully added', 1));
            $this->_redirect('*/*/guestsetup');
        }
    }

}