<?php

namespace MGS\QuickView\Block;

class Initialize extends \Magento\Framework\View\Element\Template {

    public function getConfig() {
        return [
            'baseUrl' => $this->getBaseUrl()
        ];
    }

    public function getBaseUrl() {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

}
