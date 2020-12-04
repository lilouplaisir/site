<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Mpanel\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
class Removesection extends \Magento\Framework\App\Action\Action
{
	protected $_storeManager;
	
	/**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		CustomerSession $customerSession,
		\Magento\Framework\View\Element\Context $urlContext
	)     
	{
		$this->_storeManager = $storeManager;
		$this->customerSession = $customerSession;
		$this->_urlBuilder = $urlContext->getUrlBuilder();
		parent::__construct($context);
	}
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
	
    public function execute()
    {
		if($this->getRequest()->isAjax() && ($this->customerSession->getUsePanel() == 1)){
			if($id = $this->getRequest()->getParam('id')){
				$section = $this->getModel('MGS\Mpanel\Model\Section')->load($id);
				
				if($section->getId()){
					$childs = $this->getModel('MGS\Mpanel\Model\Childs')
						->getCollection()
						->addFieldToFilter('block_name', ['like'=>$section->getName().'-%'])
						->addFieldToFilter('store_id', $section->getStoreId());
					
					if(count($childs)>0){
						foreach($childs as $_child){
							$_child->delete();
						}
					}
					
					$section->delete();
					return $this->getResponse()->setBody($id);
				}
			}
			return;
		}else{
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
			return $resultRedirect;
		}
		
    }
}
