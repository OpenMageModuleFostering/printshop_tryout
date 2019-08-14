<?php
/** Class Printshop_Checkout_CartController 
 *  This class override the functionality of Mage_Checkout_CartController
 *  @author Printshop
 */

include_once('Mage/Checkout/controllers/CartController.php');

class Printshop_Checkout_CartController extends Mage_Checkout_CartController {
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote() {
        return $this->_getCart()->getQuote();
    }

    public function addAction() {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }
            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                // save product pdf and images if they are present..
                if((isset($params['tplImage']))&& (isset($params['tplPdf']))) {
                    $quote = $cart->getQuote(); //Get all info of current quote
                    $quote_items = $quote->getAllItems(); //Get all products in cart
                    foreach($quote_items as $item) {  //loop through products
                        $item_id = $item->getProductId();  //Get product sku
                        if($item_id == $product->getId()) { //Check if sku's match
                            $item->setProductPdf($params['tplPdf']); //Set the product PDF
                            $item->setProductLowrespdf($params['tplLowresPdf']); //Set the product low resolution PDF
                            $item->setProductImage($params['tplImage']); //Set the product Image
                            $item->save();  //Update the item
                        }
                    }
                    $quote->save(); //Save cart
                }
                
                // save product pdf and images ENDS Here..

                $this->_goBack();

            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }
}
