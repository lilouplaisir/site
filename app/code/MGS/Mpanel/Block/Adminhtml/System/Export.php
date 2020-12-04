<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Adminhtml\System;

/**
 * Export CSV button for shipping table rates
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
class Export extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Backend\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
		\Magento\Framework\Filesystem $filesystem,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_backendUrl = $backendUrl;
		$this->_filesystem = $filesystem;
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');
		
		$dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('design/frontend/Mgs/');
		$html = '';
		if(is_dir($dir)) {
			if ($dh = opendir($dir)) {
				$dirs = scandir($dir);
				foreach($dirs as $theme){
					if(($theme !='') && ($theme!='.') && ($theme!='..')){
						$params = ['theme' => $theme, 'store' => $buttonBlock->getRequest()->getParam('store')];
						$url = $this->_backendUrl->getUrl("adminhtml/mpanel/export", $params);
						$html .= '<button type="button" class="action-default scalable" onclick="setLocation(\''.$url.'\')" data-ui-id="widget-button-0" style="margin-right:15px"><span>'.__('To %1', $theme).'</span></button>';
					}
				}
			}
		}

        return $html;
    }
}
