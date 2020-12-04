<?php


namespace MGS\Mpanel\Controller\Index;


use Magento\Framework\Controller\ResultFactory;

use Magento\Customer\Model\Session as CustomerSession;

class Newsection extends \Magento\Framework\App\Action\Action

{
	
	protected $_storeManager;

	
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
			
			$storeId = $this->_storeManager->getStore()->getId();
			
			$lastSection = $this->getModel('MGS\Mpanel\Model\Section')->getCollection()->addFieldToFilter('store_id', $storeId)->setOrder('block_position', 'DESC')->getFirstItem();
			
			if($lastSection->getId()){
				
				$lastPosition = $lastSection->getBlockPosition() + 1;
				
			}else{
				
				$lastPosition = 1;
				
			}
			
			$section = $this->getModel('MGS\Mpanel\Model\Section');
			
			$section->setBlockCols(12)->setStoreId($storeId)->setBlockPosition($lastPosition);
			
			if($pageId = $this->getRequest()->getParam('page_id')){
				
				$section->setPageId($pageId);
				
			}
			
			try {
				
				$section->save();
				
				$name = 'block'.$section->getId();
				
				$section->setName($name)->save();
				
				$html = '<section class="builder-container section-builder sort-item" id="panel-section-'.$section->getId().'">
					<div class="container container-panel">
						<div class="edit-panel parent-panel">
							<ul>
								<li class="up-link"><a class="moveuplink" href="#" onclick="return false;" title="Move Up"><em class="fa fa-arrow-up">&nbsp;</em></a></li>
								<li class="down-link"><a class="movedownlink" href="#" onclick="return false;" title="Move Down"><em class="fa fa-arrow-down">&nbsp;</em></a></li>
								<li><a title="'.__('Edit').'" class="popup-link" href="'.$this->_urlBuilder->getUrl('mpanel/edit/section', ['id'=>$section->getId()]).'"><em class="fa fa-gear"></em></a></li>
								<li><a onclick="if(confirm(\''.__('Are you sure you would like to remove this section?').'\')) removeSection('.$section->getId().'); return false" title="Delete" href="#"><em class="fa fa-close"></em></a></li>
							</ul>
						</div>															
						<div class="row">
							<div class="col-lg-12 col-md-12  col-builder">
								<div class="row content-panel empty-block">
									<div class="add-new-block col-md-12">';
									
									
										if($this->getRequest()->getParam('page_id')){
											
											$html .= '<a href="'.$this->_urlBuilder->getUrl('mpanel/create/block', ['page_id'=>$pageId,'name'=>$name.'-0']).'" title=" '.__('Add New Block').'" class="btn btn-primary popup-link btn-new-block">';
											
										}else{
									
									
											$html .= '<a href="'.$this->_urlBuilder->getUrl('mpanel/create/block', ['name'=>$name.'-0']).'" title=" '.__('Add New Block').'" class="btn btn-primary popup-link btn-new-block">';
											
										}
										
										
										$html .= '<em class="fa fa-plus"></em> '.__('Add New Block').'	
										
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>';
				
				return $this->getResponse()->setBody($html);
				
			} catch (Exception $e) {
				
				return $this->getResponse()->setBody($e->getMessage());
				
			}
			
			return;
			
		}else{
			
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
			
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
			
			return $resultRedirect;
			
		}
		
    }
	
}
