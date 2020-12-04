<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Cms;

/**
 * Main contact form block
 */
class Block extends \Magento\Cms\Block\Block
{	
	/**
     * Prepare Content HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $blockId = $this->getBlockId();
		$canUsePanel = $this->getCanUsePanel();
		$canUsePanelXml = $this->getEnabledBuilder();
		
		if($canUsePanelXml == "1"){
			$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\Mpanel\Helper\Data');
			$canUsePanel = $helper->acceptToUsePanel();
		}
		
        $html = '';
        if ($blockId) {
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var \Magento\Cms\Model\Block $block */
            $block = $this->_blockFactory->create();
            $block->setStoreId($storeId)->load($blockId);
            if ($block->isActive()) {
				if($canUsePanel){
					$html .= '<span class="builder-container child-builder static-can-edit">
						<span class="edit-panel child-panel">
							<ul>
								<li><a title="'.__('Edit').'" class="popup-link" href="'.str_replace('https:','',str_replace('http:','',$this->getUrl('mpanel/edit/staticblock',['cms'=>'block', 'id'=>$block->getIdentifier()]))).'"><em class="fa fa-edit">&nbsp;</em></a></li>
							</ul>
						</span>
						<span id="static_'.$block->getIdentifier().'">';
				}
				
                $html .= $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
				
				if($canUsePanel){
					$html .= '</span></span>';
				}
            }
        }
        return $html;
    }
}

