<?php
/**
/**
 * One page checkout success page
 *
 * @category   Printshop
 * @package    Printshop_Checkout
 * @author     Printshop
 */
class Printshop_Checkout_Block_Onepage_Success extends Mage_Checkout_Block_Success {
    /**
     * @deprecated after 1.4.0.1
     */
    private $_order;

    /**
     * Retrieve identifier of created order
     *
     * @return string
     * @deprecated after 1.4.0.1
     */
    public function getOrderId() {
        return $this->_getData('order_id');
    }

    /**
     * Check order print availability
     *
     * @return bool
     * @deprecated after 1.4.0.1
     */
    public function canPrint() {
        return $this->_getData('can_view_order');
    }

    /**
     * Get url for order detale print
     *
     * @return string
     * @deprecated after 1.4.0.1
     */
    public function getPrintUrl() {
        return $this->_getData('print_url');
    }

    /**
     * Get url for view order details
     *
     * @return string
     * @deprecated after 1.4.0.1
     */
    public function getViewOrderUrl() {
        return $this->_getData('view_order_id');
    }

    /**
     * See if the order has state, visible on frontend
     *
     * @return bool
     */
    public function isOrderVisible() {
        return (bool)$this->_getData('is_order_visible');
    }

    /**
     * Getter for recurring profile view page
     *
     * @param $profile
     */
    public function getProfileUrl(Varien_Object $profile) {
        return $this->getUrl('sales/recurring_profile/view', array('profile' => $profile->getId()));
    }

    /**
     * Initialize data and prepare it for output
     */
    protected function _beforeToHtml() {
        $this->_prepareLastOrder();
        $this->_prepareLastBillingAgreement();
        $this->_prepareLastRecurringProfiles();
        return parent::_beforeToHtml();
    }

    /**
     * Get last order ID from session, fetch it and check whether it can be viewed, printed etc
     */
    protected function _prepareLastOrder() {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                $isVisible = !in_array($order->getState(),
                        Mage::getSingleton('sales/order_config')->getInvisibleOnFrontStates());
                $this->addData(array(
                        'is_order_visible' => $isVisible,
                        'view_order_id' => $this->getUrl('sales/order/view/', array('order_id' => $orderId)),
                        'print_url' => $this->getUrl('sales/order/print', array('order_id'=> $orderId)),
                        'can_print_order' => $isVisible,
                        'can_view_order'  => Mage::getSingleton('customer/session')->isLoggedIn() && $isVisible,
                        'order_id'  => $order->getIncrementId(),
                ));
            }

            // Load quote and check for product and PDF & Image..

            $storeId = $order->getStore()->getId();
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                    ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
            $fromName = Mage::getStoreConfig('trans_email/ident_custom1/name');
            $fromEmail = Mage::getStoreConfig('trans_email/ident_custom1/email');
            $toName = Mage::getStoreConfig('trans_email/ident_sales/name');
            $toEmail = Mage::getStoreConfig('trans_email/ident_sales/email');
            $customerName = Mage::getSingleton('customer/session')->getCustomer()->getName();
            $customerEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
			
			// if customer is a guest user..
			if($customerEmail == ""){
				$customerName = $order->getBillingAddress()->getName();
				$customerEmail = $order->getBillingAddress()->getEmail();
			}

            $store = Mage::app()->getStore();
            $emailTemplateVariables = array();
            $emailTemplateVariables['order'] = $order;
            $emailTemplateVariables['payment_html']= $paymentBlock->toHtml();
            $emailTemplateVariables['store'] = $store;
            //get locale code to send email template in different languages..
            $locale = Mage::app()->getLocale()->getLocaleCode();
            //$emailTemplate  = Mage::getModel('core/email_template')->loadDefault('custom_email_template1', 'en_US');
			
			$emailTemplate  = Mage::getModel('core/email_template')->loadDefault('custom_email_template1', $locale);

            //******************** Admin email *************************//

            // get PDF link..

            $quoteId = $order->getQuoteId();
            $getQuoteData = Mage::getModel('sales/quote')->load($quoteId);
            $itemCollection = $getQuoteData->getAllItems();
            
            $linkto = '<tr><td>&nbsp;</td></tr>';
            foreach($itemCollection as $_itemData) {
                if($_itemData->getProductPdf() != NULL) {
                    $linkto = '<tr><td>&nbsp;</td></tr>';
                    $str = '<tr><td><a href="'.$_itemData->getProductPdf().'" >Click here</a> to download PDF</td></tr>';
                    $linkto .= $str;

                }
            }
            $emailTemplateVariables['pdflink'] = '';
            $emailTemplateVariables['pdflink']	 = $linkto;
       
			$translate  = Mage::getSingleton('core/translate');

            $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
            $mail = Mage::getModel('core/email');
            $mail->setToName($toName);
            $mail->setToEmail($toEmail);
            $mail->setBody(utf8_decode($processedTemplate));
            $mail->setSubject("Order Information");
            $mail->setFromEmail($fromEmail);
            $mail->setFromName($fromName);
            $mail->setType('html');
            try {
                $mail->send();

            }
            catch (Exception $e) {
                echo $e;
                exit;
                Mage::logException($e);
                return false;
            }


            //******************** Customer email *************************//


            $linkto = '<tr><td>&nbsp;</td></tr>';
            foreach($itemCollection as $_itemData) {
                if($_itemData->getProductLowrespdf() != NULL) {
                    $linkto = '<tr><td>&nbsp;</td></tr>';
                    $str = '<tr><td><a href="'.$_itemData->getProductLowrespdf().'" >Click here</a> to download PDF</td></tr>';
                    $linkto .= $str;

                }
            }

            $linkto .= '<tr><td>&nbsp;</td></tr>';
            $emailTemplateVariables['pdflink'] = '';
            $emailTemplateVariables['pdflink']	 = $linkto;

            $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
            $mail = Mage::getModel('core/email');
            $mail->setToName($customerName);
            $mail->setToEmail($customerEmail);
            $mail->setBody(utf8_decode($processedTemplate));
            $mail->setSubject("Your Order Information");
            $mail->setFromEmail($fromEmail);
            $mail->setFromName($fromName);
            $mail->setType('html');
            // try {
            $mail->send();

            //}
            /*catch (Exception $e) {
                        echo $e;exit;
                        Mage::logException($e);
                        return false;
                    }*/
			$translate->setTranslateInline(true);
        }
    }

    /**
     * Prepare billing agreement data from an identifier in the session
     */
    protected function _prepareLastBillingAgreement() {
        $agreementId = Mage::getSingleton('checkout/session')->getLastBillingAgreementId();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($agreementId && $customerId) {
            $agreement = Mage::getModel('sales/billing_agreement')->load($agreementId);
            if ($agreement->getId() && $customerId == $agreement->getCustomerId()) {
                $this->addData(array(
                        'agreement_ref_id' => $agreement->getReferenceId(),
                        'agreement_url' => $this->getUrl('sales/billing_agreement/view',
                        array('agreement' => $agreementId)
                        ),
                ));
            }
        }
    }

    /**
     * Prepare recurring payment profiles from the session
     */
    protected function _prepareLastRecurringProfiles() {
        $profileIds = Mage::getSingleton('checkout/session')->getLastRecurringProfileIds();
        if ($profileIds && is_array($profileIds)) {
            $collection = Mage::getModel('sales/recurring_profile')->getCollection()
                    ->addFieldToFilter('profile_id', array('in' => $profileIds))
            ;
            $profiles = array();
            foreach ($collection as $profile) {
                $profiles[] = $profile;
            }
            if ($profiles) {
                $this->setRecurringProfiles($profiles);
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $this->setCanViewProfiles(true);
                }
            }
        }
    }
}
