<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Mpanel\Controller\Index;
use Magento\Framework\Controller\ResultFactory;

class Switchbuilder extends \Magento\Framework\App\Action\Action
{
    public function getModel($model){
		return $this->_objectManager->create($model);
	}
	
    public function execute()
    {
		if($storeId = $this->getRequest()->getParam('store_id')){
			$status = $this->getRequest()->getParam('status');
			if(($status == 0) || ($status == 1)){
				$storePanel = $this->getModel('MGS\Mpanel\Model\Store')
					->getCollection()
					->addFieldToFilter('store_id', $storeId)
					->getFirstItem();
					$storeModel = $this->getModel('MGS\Mpanel\Model\Store');
					$storeModel->setStatus($status);
					if($storePanel->getId()){
						$storeModel->setId($storePanel->getId());
					}else{
						$storeModel->setStoreId($storeId);
					}
				try{
					$storeModel->save();
				}catch(Exception $e){
					$this->messageManager->addError($e->getMessage());
				}
			}
		}
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
