<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Controller\Search;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
class Save extends \Magento\Framework\App\Action\Action
{
	/**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;
	
	/**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Config\Model\Config\Factory $configFactory,
		CustomerSession $customerSession
	)     
	{
		$this->customerSession = $customerSession;
		parent::__construct($context);
		$this->_configFactory = $configFactory;
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
			$section = 'mpanel';
			$website = NULL;
			$store = NULL;
			
			$groups = [
				'catalogsearch'=> [
					'fields' => [
						'layout' => [
							'value' => $data['page_layout']
						],
						'product_per_row' => [
							'value' => $data['per_row']
						]
					]
				]
			];

			$configData = [
				'section' => $section,
				'website' => $website,
				'store' => $store,
				'groups' => $groups
			];

			/** @var \Magento\Config\Model\Config $configModel  */
			$configModel = $this->_configFactory->create(['data' => $configData]);
			$configModel->save();
			
			$this->messageManager->addSuccess(__('Your changes have been saved. If you do not see your changes, please refresh cache.'));
		}
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
