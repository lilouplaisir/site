<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Mpanel\Controller\Post;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;

class Content extends \Magento\Framework\App\Action\Action
{
	protected $_storeManager;
	
	/**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Config\Model\Config\Factory $configFactory,
		CustomerSession $customerSession
	)     
	{
		$this->customerSession = $customerSession;
		parent::__construct($context);
		$this->_configFactory = $configFactory;
		$this->_storeManager = $storeManager;
	}
    
    public function execute()
    {
		if($this->customerSession->getUsePanel() == 1){
			$data = $this->getRequest()->getPostValue();
			$fields[$data['type']] = ['value'=>$data['value']];
			
			$groups['general'] = [
				'fields' => $fields
			];
			
			$configData = [
				'section' => 'mgstheme',
				'website' => NULL,
				'store' => $this->_storeManager->getStore()->getId(),
				'groups' => $groups
			];

			/** @var \Magento\Config\Model\Config $configModel  */
			$configModel = $this->_configFactory->create(['data' => $configData]);
			$configModel->save();
			return $this->getMessageHtml('success', __('You have changed the %1. Please wait to reload page.', $data['type']), true);
		}else{
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
			return $resultRedirect;
		}
    }
	
	public function getMessageHtml($type, $message, $reload){
		$html = '<style type="text/css">
			.container {
				padding: 0px 15px;
				margin-top:60px;
			}
			.page.messages .message {
				padding: 15px;
				font-family: "Lato",arial,tahoma;
				font-size: 14px;
			}
			.page.messages .message-success {
				background-color: #dff0d8;
			}
			.page.messages .message-danger {
				background-color: #f2dede;
			}
		</style>';
		$html .= '<main class="page-main container">
			<div class="page messages"><div data-placeholder="messages"></div><div>
				<div class="messages">
					<div class="message-'.$type.' '.$type.' message" data-ui-id="message-'.$type.'">
						<div>'.$message.'</div>
					</div>
				</div>
			</div>
		</div></main>';
		
		if($reload){
			$html .= '<script type="text/javascript">window.parent.location.reload();</script>';
		}
		
		return $this->getResponse()->setBody($html);
	}
}
