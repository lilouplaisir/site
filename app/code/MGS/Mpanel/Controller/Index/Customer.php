<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Mpanel\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
class Customer extends \Magento\Framework\App\Action\Action
{
	/**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

	public function __construct(\Magento\Framework\App\Action\Context $context, CustomerSession $customerSession)     
	{
		$this->customerSession = $customerSession;
		parent::__construct($context);
	}
	
    public function execute()
    {
		if($this->customerSession->getId()!=''){
			return $this->customerSession->getId();
		}
		return false;
    }
}
