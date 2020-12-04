<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Panel;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Main contact form block
 */
class Toppanel extends Template
{
	
	protected $helper;
	protected $_objectManager;

	
	public function __construct(
		Template\Context $context,
		\MGS\Mpanel\Helper\Data $helper,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		array $data = []
	){
        parent::__construct($context, $data);
		$this->_isScopePrivate = true;
		$this->_objectManager = $objectManager;
		$this->helper = $helper;
    }
	
	public function getStoreId(){
		return $this->_storeManager->getStore()->getId();
	}
	
	public function getCmsPageId(){
		if($this->getRequest()->getFullActionName()=='cms_index_index'){
			$pageIdentifier = $this->helper->getStoreConfig('web/default/cms_home_page',$this->_storeManager->getStore()->getId());
			$arrIdentifier = explode('|', $pageIdentifier);
			$page = $this->getModel('Magento\Cms\Model\Page')->setStoreId($this->_storeManager->getStore()->getId())->load($arrIdentifier[0]);
			return $page->getId();
		}else{
			return $this->getRequest()->getParam('page_id');
		}
	}
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
	
	public function getHelper(){
		return $this->helper;
	}
	
	/**
     * Returns customer id from session
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->helper->getCustomerId();
    }
	
	public function getCustomer(){
		return $this->helper->getCustomer();
	}
	
	public function getPageLayoutConfig(){
		$object = new \MGS\Mpanel\Model\Config\Source\Layout;
		$result = $object->toOptionArray();
		return $result;
	}
	
	public function getPerrowConfig(){
		$result = [['value' => '', 'label' => __('Use Config')]]; 
		$object = new \MGS\Mpanel\Model\Config\Source\Perrow;
		$result = array_merge($result, $object->toOptionArray());
		return $result;
	}
	
	public function getRatioConfig(){
		$result = [['value' => '', 'label' => __('Use Config')]]; 
		$object = new \MGS\Mpanel\Model\Config\Source\Ratio;
		$result = array_merge($result, $object->toOptionArray());
		return $result;
	}
}

