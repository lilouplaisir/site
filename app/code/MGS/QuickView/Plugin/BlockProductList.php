<?php

namespace MGS\QuickView\Plugin;

class BlockProductList {

    const XML_PATH_QUICKVIEW_ENABLED = 'mgs_quickview/general/enabled';
    const XML_PATH_QUICKVIEW_BUTTONSTYLE = 'mgs_quickview/general/button_style';

    protected $urlInterface;
    protected $scopeConfig;

    public function __construct(
    \Magento\Framework\UrlInterface $urlInterface, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->urlInterface = $urlInterface;
        $this->scopeConfig = $scopeConfig;
    }

    public function aroundGetProductDetailsHtml(
    \Magento\Catalog\Block\Product\ListProduct $subject, \Closure $proceed, \Magento\Catalog\Model\Product $product
    ) {
        $result = $proceed($product);
        $isEnabled = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($isEnabled) {
            $buttonStyle = 'mgs_quickview_button_' . $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_BUTTONSTYLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $productUrl = $this->urlInterface->getUrl('mgs_quickview/catalog_product/view', array('id' => $product->getId()));
            return $result . '<a class="mgs-quickview ' . $buttonStyle . '" data-quickview-url=' . $productUrl . ' href="javascript:void(0);"><span>' . __("Quick View") . '</span></a>';
        }
        return $result;
    }

}
