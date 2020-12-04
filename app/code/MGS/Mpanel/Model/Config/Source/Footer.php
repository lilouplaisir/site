<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\Mpanel\Model\Config\Source;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Footer implements \Magento\Framework\Option\ArrayInterface
{
	protected $helper;
	protected $request;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper
     * @param array $data
     */
    public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\Request\Http $request,
		\MGS\Mpanel\Helper\Data $helper
    ) {
		$this->_scopeConfig = $scopeConfig;
		$this->helper = $helper;
		$this->request = $request;
    }
	
	public function getStoreConfig($node){
		return $this->_scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);
	}
	
	public function getRequest(){
		return $this->request;
	}
	
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		$themeId = $this->_scopeConfig->getValue('design/theme/theme_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);
		if($websiteId = $this->getRequest()->getParam('website')){
			$themeId = $this->_scopeConfig->getValue('design/theme/theme_id', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $websiteId);
		}
		if($storeId = $this->getRequest()->getParam('store')){
			$themeId = $this->_scopeConfig->getValue('design/theme/theme_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
		}
		
		$result = $this->helper->getContentVersion('footers', $themeId);
		
        return $result;
    }
}
