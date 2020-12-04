<?php

namespace MGS\Guestwishlist\Controller\Index;

use Magento\Checkout\Model\Cart as CustomerCart;

class Remove extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var BelVG\GuestWishlist\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MGS\Guestwishlist\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context, 
            \Magento\Store\Model\StoreManagerInterface $storeManager, 
            \MGS\Guestwishlist\Helper\Data $helper, 
            \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager, 
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $removeAll = $this->getRequest()->getParam('removeAll', false);
            if($removeAll) {
                $metadata = $this->_cookieMetadataFactory
                    ->createPublicCookieMetadata()
                    ->setPath('/')
                    ->setDuration(-3600);
                $this->_cookieManager->setPublicCookie(
                    \Convert\GuestWishlist\Helper\Data::COOKIE_NAME,
                    null,
                    $metadata
                );
                $this->messageManager->addSuccess(__('All Items have been removed.'));
                $resultRedirect->setUrl($this->_url->getUrl('guestwishlist'));
                return $resultRedirect;
            }
            
            $cookie = $this->_helper->getCookie(\MGS\Guestwishlist\Helper\Data::COOKIE_NAME) != null 
                    ? $this->_helper->getCookie(\MGS\Guestwishlist\Helper\Data::COOKIE_NAME) : [];                    
            
            $itemId = $this->getRequest()->getParam('itemId', false);
            $cookie = $this->removeItemById($itemId, $cookie);
            
            $metadata = $this->_cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setPath('/')
                ->setDuration(86400);
            $this->_cookieManager->setPublicCookie(
                \MGS\Guestwishlist\Helper\Data::COOKIE_NAME,
                serialize($cookie),
                $metadata
            );
            
            $this->messageManager->addSuccess(__('An Item has been removed.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something wrong.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        
        $resultRedirect->setUrl($this->_url->getUrl('guestwishlist'));
        return $resultRedirect;
    }
    
    /**
     * 
     * @param string $itemId
     * @return [] array of wishlist
     */
    protected function removeItemById($itemId, $wishlist) {
        if ($wishlist !== null && is_array($wishlist)) {
            foreach ($wishlist as $productId => $items) {
                foreach ($items as $key => $_item) {
                    if ($itemId == $key) {
                        unset($wishlist[$productId][$itemId]);
                        // clean empty parent
                        // unset parent if does not have any child products
                        if (empty($wishlist[$productId])) {
                            unset($wishlist[$productId]);
                        }
                    }
                }
            }
        }
        return $wishlist;
    }
}
