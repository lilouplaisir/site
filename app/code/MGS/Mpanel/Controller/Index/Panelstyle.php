<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Mpanel\Controller\Index;

class Panelstyle extends \Magento\Framework\App\Action\Action
{
    
    public function execute()
    {
		$html = '';
		$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\Mpanel\Helper\Data');
		
		$html = $helper->getPanelStyle();
		$imageUrl = $helper->getViewFileUrl('MGS_Mpanel::images/');
		$html = str_replace('{{image_url}}',$imageUrl,$html);

		$this->getResponse()->setHeader('Content-type', 'text/css', true);
		$this->getResponse()->setBody($html);
    }
}
