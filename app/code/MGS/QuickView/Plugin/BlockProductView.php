<?php

namespace MGS\QuickView\Plugin;

class BlockProductView {

    const XML_PATH_QUICKVIEW_HIDE_QTY = 'mgs_quickview/general/hide_qty';

    protected $scopeConfig;
    protected $request;

    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\App\Request\Http $request) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    public function afterShouldRenderQuantity(
    \Magento\Catalog\Block\Product\View $subject, $result) {
        if ($this->request->getFullActionName() == 'mgs_quickview_catalog_product_view') {
            $hideQtySelector = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_HIDE_QTY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return !$hideQtySelector;
        }
        return $result;
    }

}
