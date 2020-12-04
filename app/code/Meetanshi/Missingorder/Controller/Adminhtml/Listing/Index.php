<?php

namespace Meetanshi\Missingorder\Controller\Adminhtml\Listing;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        try {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->getBlock('missingoreder.grid');
            $resultPage->getConfig()->getTitle()->prepend(__('Manage Missing Orders'));
            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }

    protected function _isAllowed()
    {
        return true;
    }
}
