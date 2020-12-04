<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Controller\Category;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
class Save extends \Magento\Framework\App\Action\Action
{
	protected $_categoryFactory;
	
	protected $_repository;
	
	/**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context, 
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Catalog\Api\CategoryRepositoryInterface $repository,
		CustomerSession $customerSession
	)     
	{
		$this->customerSession = $customerSession;
		$this->_categoryFactory = $categoryFactory;
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
			$currentCategory = $this->_categoryFactory->create()->load($data['id']);
			$currentCategory->setStoreId(0);
			$currentCategory->setPageLayout($data['page_layout']);
			$currentCategory->setPerRow($data['per_row']);
			$currentCategory->setPictureRatio($data['picture_ratio']);
			
			$this->_repository->save($currentCategory);
			$this->messageManager->addSuccess(__('Your changes have been saved. If you do not see your changes, please refresh cache.'));
		}
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
