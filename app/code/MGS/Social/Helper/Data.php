<?php

namespace MGS\Social\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

class Data extends AbstractHelper
{
    protected $storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    )
    {
		
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getConfig($key, $store = null)
    {
        if ($store == null || $store == '') {
            $store = $this->storeManager->getStore()->getId();
        }
        $store = $this->storeManager->getStore($store);
        $config = $this->scopeConfig->getValue(
            'social/' . $key,
            ScopeInterface::SCOPE_STORE,
            $store);
        return $config;
    }
	
    public function getUrl($value)
    {
        return $this->_getUrl($value, ['_secure' => true]);
    }
	public function getStoreConfig($node){
		return $this->_scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

}