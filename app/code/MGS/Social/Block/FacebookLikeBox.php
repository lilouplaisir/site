<?php

namespace MGS\Social\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;


class FacebookLikeBox extends Template{
	
	
	public function __construct(
        Context $context,
		\MGS\Social\Helper\Data $helper
    )
    {       
		$this->helper = $helper;
        parent::__construct($context);
    }
	
	public function getFacebookLikeBox() {
        $pageUrl = $this->getPageUrl();
        $width = $this->getWidth();
        $height = $this->getHeight();
		$pageTab = $this->getTabs();
        if ($this->getUseSmallHeader()) {
            $useSmallHeader = 'true';
        } else {
            $useSmallHeader = 'false';
        }
        if ($this->getDataAdaptContainerWidth()) {
            $dataAdaptContainerWidth = 'true';
        } else {
            $dataAdaptContainerWidth = 'false';
        }
        if ($this->getDataHideCover()) {
            $dataHideCover = 'true';
        } else {
            $dataHideCover = 'false';
        }
        if ($this->getDataShowFacepile()) {
            $dataShowFacepile = 'true';
        } else {
            $dataShowFacepile = 'false';
        }
        if ($this->getDataShowPosts()) {
            $dataShowPosts = 'true';
        } else {
            $dataShowPosts = 'false';
        }
        if ($pageUrl != '' && $width != '' && $height != '') {
            return '<div class="fb-page" data-tabs="'. $pageTab . '" data-href="'. $pageUrl . '" data-width="' . $width . '" data-height="' . $height . '" data-small-header="' . $useSmallHeader . '" data-adapt-container-width="' . $dataAdaptContainerWidth . '" data-hide-cover="' . $dataHideCover . '" data-show-facepile="' . $dataShowFacepile . '" data-show-posts="' . $dataShowPosts . '"><div class="fb-xfbml-parse-ignore"><blockquote cite="' . $pageUrl . '"><a href="' . $pageUrl . '">' . $this->getTitle() . '</a></blockquote></div></div>';
        } else {
            return null;
        }
    }
	
	public function getTabs() {
		return $this->helper->getConfig('facebook_page_plugin_settings/tabs');
	}
	
	public function getPageUrl() {
		return $this->helper->getConfig('facebook_page_plugin_settings/href');
	}
	
	public function getWidth() {
		return $this->helper->getConfig('facebook_page_plugin_settings/width');
	}
	
	public function getHeight() {
		return $this->helper->getConfig('facebook_page_plugin_settings/height');
	}
	
	public function getUseSmallHeader() {
		return $this->helper->getConfig('facebook_page_plugin_settings/small_header');
	}
	
	public function getDataAdaptContainerWidth() {
		return $this->helper->getConfig('facebook_page_plugin_settings/adapt_container_width');
	}
	
	public function getDataHideCover() {
		return $this->helper->getConfig('facebook_page_plugin_settings/hide_cover');
	}
	
	public function getDataShowFacepile() {
		return $this->helper->getConfig('facebook_page_plugin_settings/show_facepile');
	}
	
	public function getDataShowPosts() {
		return $this->helper->getConfig('facebook_page_plugin_settings/small_header');
	}
}
?>