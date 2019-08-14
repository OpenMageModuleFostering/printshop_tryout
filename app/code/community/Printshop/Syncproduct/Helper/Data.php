<?php
/** Class Printshop_Syncproduct_Helper_Data
 * @category    Local
 * @author Printshop
 */

class Printshop_Syncproduct_Helper_Data extends Mage_Core_Helper_Abstract {
    public function designToolDetails() {
        $objIbright = Mage::getModel('syncproduct/ibrightfrontend');
        $getIbrightDetails = $objIbright->load('1');
        return $getIbrightDetails;
    }
	public function guestDesignToolDetails() {
        $objIbright = Mage::getModel('syncproduct/ibrightguest');
        $getIbrightDetails = $objIbright->load('1');
        return $getIbrightDetails;
    }
    public function adminConnection() {
        $objIbright = Mage::getModel('syncproduct/ibrightadmin');
        $getIbrightDetails = $objIbright->load('1');
        return $getIbrightDetails;
    }
    public function productImage($pdtId, $userId) {
        $objIbright = Mage::getModel('syncproduct/catalog');
        $getIbrightDetails = $objIbright->getCollection()
										->addFieldToFilter('user_id', $userId)
			->addFieldToFilter('product_id', $pdtId)
			->load();
		foreach($getIbrightDetails as $data){
			$pdtImage = $data->getImageUrl();
		}
        return $pdtImage;
    }
	public function productHighResPdf($pdtId, $userId) {
        $objIbright = Mage::getModel('syncproduct/catalog');
        $getIbrightDetails = $objIbright->getCollection()
										->addFieldToFilter('user_id', $userId)
			->addFieldToFilter('product_id', $pdtId)
			->load();
		foreach($getIbrightDetails as $data){
			$pdtHighResPdf = $data->getPdfUrl();
		}
        return $pdtHighResPdf;
    }
	public function productLowResPdf($pdtId, $userId) {
        $objIbright = Mage::getModel('syncproduct/catalog');
        $getIbrightDetails = $objIbright->getCollection()
										->addFieldToFilter('user_id', $userId)
			->addFieldToFilter('product_id', $pdtId)
			->load();
		foreach($getIbrightDetails as $data){
			$pdtLowResPdf = $data->getPdfLowresurl();
		}
        return $pdtLowResPdf;
    }
	public function customTemplateId($pdtId, $userId) {
        $objIbright = Mage::getModel('syncproduct/catalog');
        $getIbrightDetails = $objIbright->getCollection()
										->addFieldToFilter('user_id', $userId)
			->addFieldToFilter('product_id', $pdtId)
			->load();
		foreach($getIbrightDetails as $data){
			$customId = $data->getId();
		}
        return $customId;
    }
}