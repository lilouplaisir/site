<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Mpanel\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
class Disable extends \Magento\Framework\App\Action\Action
{
	/**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

	public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Element\Context $urlContext, CustomerSession $customerSession)     
	{
		$this->_urlBuilder = $urlContext->getUrlBuilder();
		$this->customerSession = $customerSession;
		parent::__construct($context);
	}
	
	public function urlDecode($url)
    {
        $url = base64_decode(strtr($url, '-_,', '+/='));
        return $this->_urlBuilder->sessionUrlVar($url);
    }
	
    public function execute()
    {
		$referer = $this->getRequest()->getParam('referrer');
		if($referer!=''){
			$url = $this->urlDecode($referer);
			$this->customerSession->setUsePanel(false);
			
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setUrl($url);
			return $resultRedirect;
		}else{
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
			return $resultRedirect;
		}
        
    }
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
}
