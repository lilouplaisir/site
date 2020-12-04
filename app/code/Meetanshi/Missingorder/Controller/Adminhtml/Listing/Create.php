<?php

namespace Meetanshi\Missingorder\Controller\Adminhtml\Listing;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Backend\Model\Session\Quote as BackendSession;

class Create extends Action
{
    protected $resultPageFactory;
    private $orderCollectionFactory;
    protected $quoteFactory;
    private $product;
    protected $backendSession;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        QuoteFactory $quoteFactory,
        OrderFactory $orderCollectionFactory,
        ProductFactory $product,
        BackendSession $backendSession
    )
    {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->quoteFactory = $quoteFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->product = $product;
        $this->backendSession = $backendSession;
    }

    public function execute()
    {
        if ($postData = $this->getRequest()->getParams('id')) {
            try {
                $params = $this->getRequest()->getParams('id');
                $collection = $this->orderCollectionFactory->create()->load($params['id'],'quote_id');

                if ($collection->getData() != null) {
                    $id = 0;
                } else {
                    $id = 1;
                }

                $quote = $this->quoteFactory->create()->loadByIdWithoutStore($params['id']);
                $storeId = $quote->getData('store_id');

                if ($id) {
                    if ($quote->getData('customer_id') != null){
                        $this->backendSession->setCustomerId($quote->getData('customer_id'));
                    }else{
                        $this->backendSession->setCustomerId($quote->getData('customer_id'));
                    }
                    $this->backendSession->setQuoteId($params['id']);
                    $this->backendSession->setStoreId($storeId);

                    $quote->setReservedOrderId('');
                    $quote->save();
                }
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('sales/order_create');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
    }

    protected function _isAllowed()
    {
        return true;
    }
}
