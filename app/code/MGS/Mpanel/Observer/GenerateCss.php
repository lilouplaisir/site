<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Observer;

use Magento\Framework\Event\ObserverInterface;

class GenerateCss implements ObserverInterface
{
	protected $_helper;
	
	public function __construct(
		\MGS\Mpanel\Helper\Data $helper
    ) {
		$this->_helper = $helper;
    }
	
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_helper->generateCssForAll();
    }
}
