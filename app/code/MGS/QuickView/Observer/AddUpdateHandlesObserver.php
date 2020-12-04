<?php

namespace MGS\QuickView\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddUpdateHandlesObserver implements ObserverInterface {

    protected $scopeConfig;

    const XML_PATH_QUICKVIEW_HIDE_PRODUCT_IMAGE = 'mgs_quickview/general/hide_product_image';
    const XML_PATH_QUICKVIEW_HIDE_PRODUCT_IMAGE_THUMB = 'mgs_quickview/general/hide_product_image_thumb';
    const XML_PATH_QUICKVIEW_HIDE_AVAILABILITY = 'mgs_quickview/general/hide_availability';
    const XML_PATH_QUICKVIEW_HIDE_PRODUCT_SOCIAL_LINKS = 'mgs_quickview/general/hide_product_social_links';

    //const XML_PATH_QUICKVIEW_HIDE_PRODUCT_DETAILS = 'mgs_quickview/general/hide_product_details';

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $layout = $observer->getData('layout');
        $fullActionName = $observer->getData('full_action_name');
        if ($fullActionName != 'mgs_quickview_catalog_product_view') {
            return $this;
        }
        $hideProductImage = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_HIDE_PRODUCT_IMAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($hideProductImage) {
            $layout->getUpdate()->addHandle('mgs_quickview_hide_product_image');
        }
        $hideProductImageThumb = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_HIDE_PRODUCT_IMAGE_THUMB, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($hideProductImageThumb) {
            $layout->getUpdate()->addHandle('mgs_quickview_hide_product_image_thumb');
        }
        $hideAvailability = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_HIDE_AVAILABILITY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($hideAvailability) {
            $layout->getUpdate()->addHandle('mgs_quickview_hide_availability');
        }
        $hideProductSocialLinks = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_HIDE_PRODUCT_SOCIAL_LINKS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($hideProductSocialLinks) {
            $layout->getUpdate()->addHandle('mgs_quickview_hide_product_social_links');
        }
        //$hideProductDetails = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_HIDE_PRODUCT_DETAILS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        //if ($hideProductDetails) {
        //    $layout->getUpdate()->addHandle('mgs_quickview_hide_product_details');
        //}
        return $this;
    }

}
