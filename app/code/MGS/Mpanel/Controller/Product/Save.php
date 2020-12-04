<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Controller\Product;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
class Save extends \Magento\Framework\App\Action\Action
{
	protected $_productFactory;
	
	protected $_repository;
	
	/**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context, 
		\Magento\Catalog\Model\ProductFactory $_productFactory,
		\Magento\Catalog\Api\ProductRepositoryInterface $repository,
		CustomerSession $customerSession
	)     
	{
		$this->customerSession = $customerSession;
		$this->_productFactory = $_productFactory;
		$this->_repository = $repository;
		parent::__construct($context);
		$this->messageManager = $context->getMessageManager();
	}
	
    /**
     * Category view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
		if($this->customerSession->getUsePanel()){
			$data = $this->getRequest()->getPostValue();
			$currentProduct = $this->_productFactory->create()->load($data['id']);
			$currentProduct->setStoreId(0);
			$currentProduct->setPageLayout($data['page_layout']);
			
			$this->_repository->save($currentProduct);
			$this->messageManager->addSuccess(__('Your changes have been saved. If you do not see your changes, please refresh cache.'));
		}
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
