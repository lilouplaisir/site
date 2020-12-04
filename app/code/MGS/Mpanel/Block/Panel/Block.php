<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Panel;

use MGS\Mpanel\Block\Panel\AbstractPanel;

/**
 * Main contact form block
 */
class Block extends AbstractPanel
{
	public function getBlocks(){
		$blockName = $this->getBlockName();
		$storeId = $this->_storeManager->getStore()->getId();
		if($this->getRequest()->getFullActionName()=='cms_index_index'){
			$blocks = $this->getModel('MGS\Mpanel\Model\Childs')
				->getCollection()
				->addFieldToFilter('block_name', $blockName)
				->addFieldToFilter('store_id', $storeId)
				->setOrder('position', 'ASC');
			$blocks->getSelect()->where('(main_table.page_id='.$this->_pageId.') or (main_table.page_id IS NULL) or (main_table.page_id =0) or (main_table.page_id = "")');
		}else{
			$blocks = $this->getModel('MGS\Mpanel\Model\Childs')
				->getCollection()
				->addFieldToFilter('block_name', $blockName)
				->addFieldToFilter('store_id', $storeId)
				->addFieldToFilter('page_id', $this->_pageId)
				->setOrder('position', 'ASC');
		}
		return $blocks;
	}
	
	public function getBlockClass($block, $setting, $canUsePanel){
		$class = 'col-md-' . $block->getCol();
		if($canUsePanel){
			$class .= ' sort-item builder-container child-builder';
		}
		if($block->getClass()!=''){
			$class .= ' '.$block->getClass();
		}
        if (isset($setting['custom_class']) && $setting['custom_class'] != '') {
            $class .= ' ' . $setting['custom_class'];
        }
        if (isset($setting['text_colour']) && $setting['text_colour'] != '') {
            $class .= ' ' . $setting['text_colour'];
        }
        if (isset($setting['link_colour']) && $setting['link_colour'] != '') {
            $class .= ' ' . $setting['link_colour'];
        }
        if (isset($setting['link_hover_colour']) && $setting['link_hover_colour'] != '') {
            $class .= ' ' . $setting['link_hover_colour'];
        }
        if (isset($setting['button_colour']) && $setting['button_colour'] != '') {
            $class .= ' ' . $setting['button_colour'];
        }
        if (isset($setting['button_hover_colour']) && $setting['button_hover_colour'] != '') {
            $class .= ' ' . $setting['button_hover_colour'];
        }
        if (isset($setting['button_text_colour']) && $setting['button_text_colour'] != '') {
            $class .= ' ' . $setting['button_text_colour'];
        }
        if (isset($setting['button_text_hover_colour']) && $setting['button_text_hover_colour'] != '') {
            $class .= ' ' . $setting['button_text_hover_colour'];
        }
        if (isset($setting['button_border_colour']) && $setting['button_border_colour'] != '') {
            $class .= ' ' . $setting['button_border_colour'];
        }
        if (isset($setting['button_border_hover_colour']) && $setting['button_border_hover_colour'] != '') {
            $class .= ' ' . $setting['button_border_hover_colour'];
        }
        if (isset($setting['price_colour']) && $setting['price_colour'] != '') {
            $class .= ' ' . $setting['price_colour'];
        }
		if (isset($setting['animation']) && $setting['animation'] != '') {
            $class .= ' animated';
        }

        return $class;
	}
	
	public function getEditChildHtml($block, $child) {
        $html = '<div class="edit-panel child-panel"><ul>';

        $html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . __('Move Block') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';

        $html .= '<li><a href="' . $this->getUrl('mpanel/create/element', array('page_id'=>$this->getPageId(), 'block' => $block, 'id' => $child->getId(), 'type' => $child->getType())) . '" class="popup-link" title="' . __('Edit') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';

        $html .= '<li class="change-col"><a href="javascript:void(0)" title="' . __('Change column setting') . '"><em class="fa fa-columns">&nbsp;</em></a><ul>';

        for ($i = 1; $i <= 12; $i++) {
            $html .= '<li><a id="changecol-'.$child->getId().'-'.$i.'" href="' . str_replace('https:','',str_replace('http:','',$this->getUrl('mpanel/element/changecol', array('id' => $child->getId(), 'col' => $i)))) . '" onclick="changeBlockCol(this.href, '.$child->getCol().', '.$child->getId().'); return false"';
			if($i == $child->getCol()){
				$html .= ' class="active"';
			}
			$html .='><span>' . $i . '/12</span></a></li>';
        }

        $html .= '</ul></li>';

        $html .= '<li><a href="' . str_replace('https:','',str_replace('http:','',$this->getUrl('mpanel/element/delete', array('id' => $child->getId())))) . '" onclick="if(confirm(\'' . __('Are you sure you would like to remove this block?') . '\')) removeBlock(this.href, '.$child->getId().'); return false" title="' . __('Delete Block') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
        $html .= '</ul></div>';

        return $html;
    }
	
	public function getContentOfBlock($block){
		return $this->_filterProvider->getBlockFilter()->setStoreId($this->_storeManager->getStore()->getId())->filter($block->getBlockContent());
	}
}

