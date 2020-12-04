<?php

namespace MGS\QuickView\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    const XML_PATH_QUICKVIEW_ENABLED = 'mgs_quickview/general/enabled';

	public function aroundQuickViewHtml(
    \Magento\Catalog\Model\Product $product
    ) {
        $result = '';
        $isEnabled = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($isEnabled) {
            $productUrl = $this->_urlBuilder->getUrl('mgs_quickview/catalog_product/view', array('id' => $product->getId()));
            return $result . '<button data-title="'. __("Quick View") .'" class="action mgs-quickview" data-quickview-url=' . $productUrl . ' title="' . __("Quick View") . '"><span class="pe-7s-search"></span></button>';
        }
        return $result;
    }

}
