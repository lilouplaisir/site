<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Promobanners\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Promobanners extends Template
{
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(Template\Context $context, array $data = [], \Magento\Framework\ObjectManagerInterface $objectManager)
    {
        parent::__construct($context, $data);
		$this->_objectManager = $objectManager;
    }
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Promobanners\Model\Promobanners');
	}
	
	public function getBannerById($id){
		if (!is_numeric($id)) {
            $banner = $this->getModel()->getCollection()->addFieldToFilter('identifier', $id)->getFirstItem();
			if($banner->getId()){
				return $banner;
			}else{
				return;
			}
        }else{
			$banner = $this->getModel()->load($id);
			return $banner;
		}
	}
	
	public function getBannerImageUrl($banner){
		$bannerUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'promobanners/'.$banner->getFilename();
		return $bannerUrl;
	}
	
	public function getCustomClass($banner){
		$class = '';
		if($banner->getCustomClass()!=''){
			$class .= ' '.$banner->getCustomClass();
		}
		if($banner->getEffect()!=''){
			$class .= ' '.$banner->getEffect();
		}
		return $class;
	}
}

