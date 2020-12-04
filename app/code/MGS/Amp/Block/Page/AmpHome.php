<?php

namespace MGS\Amp\Block\Page;

use Magento\Framework\View\Element\Template;

class AmpHome extends Template {
	
	/**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var MGS\Amp\Helper\Config
     */
    protected $_configHelper;
	
    /**
     * @var Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Zemez\Amp\Helper\Data $helper,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MGS\Amp\Helper\Config $configHelper,
		\Magento\Cms\Model\PageFactory $pageFactory,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_configHelper = $configHelper;
		$this->_filterProvider = $filterProvider;
		$this->_pageFactory = $pageFactory;
    }
	
	public function getContentAmp(){
		$content = $this->getCmsPageContent();
		return $this->_configHelper->convertHtmlForAmp($this->_filterProvider->getPageFilter()->filter($this->replaceTeamplate($content)));
	}
	
	protected function getCmsForAmp(){
		return $this->_configHelper->getStoreConfig('mgs_amp/general/cms_home_mobile');
	}
	
	protected function getCmsPageContent(){
		$csmPageContent = '';
		$cmsPage = $this->getCmsForAmp();
		if ($cmsPage) {
			$csmPageContent = $this->_pageFactory->create()->load($cmsPage, 'identifier')->getContent();
		}
		
		return $csmPageContent;
	}
	
	protected function replaceTeamplate($content){
		//$content = $this->_configHelper->convertHtmlForAmp($content);
		/* Slider Block */
		$content = str_replace('template="widget/owl_slider.phtml"','template="MGS_Amp::MGS_Mpanel/widget/owl_slider.phtml"',$content);
		
		/* Promobanner Block */
		$content = str_replace('template="banner.phtml"','template="MGS_Amp::MGS_Promobanners/banner.phtml"',$content);
		
		/* Instagram Block */
		$content = str_replace('template="widget/instagram.phtml"','template="MGS_Amp::MGS_Social/widget/instagram.phtml"',$content);
		$content = str_replace('template="widget/static_block/default.phtml" block_id="optimize_image_instagram"','template="MGS_Amp::blank.phtml"',$content);
		$content = str_replace('template="widget/static_block/default.phtml" block_id="optimize_instagram_funiture"','template="MGS_Amp::blank.phtml"',$content);
		
		/* New Product Block */
		$content = str_replace('template="products/new/grid.phtml"','template="MGS_Amp::MGS_Mpanel/products/new/grid.phtml"',$content);
		$content = str_replace('template="products/new/list.phtml"','template="MGS_Amp::MGS_Mpanel/products/new/list.phtml"',$content);
		$content = str_replace('template="products/new/category-tabs.phtml"','template="MGS_Amp::MGS_Mpanel/products/new/category-tabs.phtml"',$content);
		
		/* Top Rate Product Block */
		$content = str_replace('template="products/rate/grid.phtml"','template="MGS_Amp::MGS_Mpanel/products/rate/grid.phtml"',$content);
		$content = str_replace('template="products/rate/list.phtml"','template="MGS_Amp::MGS_Mpanel/products/rate/list.phtml"',$content);
		$content = str_replace('template="products/rate/category-tabs.phtml"','template="MGS_Amp::MGS_Mpanel/products/rate/category-tabs.phtml"',$content);
		
		/* Sale Product Block */
		$content = str_replace('template="products/sale/grid.phtml"','template="MGS_Amp::MGS_Mpanel/products/sale/grid.phtml"',$content);
		$content = str_replace('template="products/sale/list.phtml"','template="MGS_Amp::MGS_Mpanel/products/sale/list.phtml"',$content);
		$content = str_replace('template="products/sale/category-tabs.phtml"','template="MGS_Amp::MGS_Mpanel/products/sale/category-tabs.phtml"',$content);
		
		/* Attribute Product Block */
		$content = str_replace('template="products/attribute/grid.phtml"','template="MGS_Amp::MGS_Mpanel/products/attribute/grid.phtml"',$content);
		$content = str_replace('template="products/attribute/list.phtml"','template="MGS_Amp::MGS_Mpanel/products/attribute/list.phtml"',$content);
		$content = str_replace('template="products/attribute/category-tabs.phtml"','template="MGS_Amp::MGS_Mpanel/products/attribute/category-tabs.phtml"',$content);
		
		/* Category Product Block */
		$content = str_replace('template="products/category_products/grid.phtml"','template="MGS_Amp::MGS_Mpanel/products/category_products/grid.phtml"',$content);
		$content = str_replace('template="products/category_products/list.phtml"','template="MGS_Amp::MGS_Mpanel/products/category_products/list.phtml"',$content);
		$content = str_replace('template="products/category_products/category-tabs.phtml"','template="MGS_Amp::MGS_Mpanel/products/category_products/category-tabs.phtml"',$content);
		
		/* Deal Product Block */
		$content = str_replace('template="products/deals/grid.phtml"','template="MGS_Amp::MGS_Mpanel/products/deals/grid.phtml"',$content);
		$content = str_replace('template="products/deals/list.phtml"','template="MGS_Amp::MGS_Mpanel/products/deals/list.phtml"',$content);
		$content = str_replace('template="products/deals/one_item.phtml"','template="MGS_Amp::MGS_Mpanel/products/deals/grid.phtml"',$content);
		
		/* Product Tabs Block */
		$content = str_replace('template="products/tabs/grid.phtml"','template="MGS_Amp::MGS_Mpanel/products/tabs/grid.phtml"',$content);
		
		/* Single Product Block */
		$content = str_replace('template="products/single/default.phtml"','template="MGS_Amp::MGS_Mpanel/products/single/default.phtml"',$content);
		
		/* Recent Viewed Product Block */
		$content = str_replace('template="product/widget/viewed/list.phtml"','template="MGS_Amp::MGS_Mpanel/products/viewed/list.phtml"',$content);
		$content = str_replace('template="product/widget/viewed/grid.phtml"','template="MGS_Amp::MGS_Mpanel/products/viewed/grid.phtml"',$content);
		
		/* Newsletter Block */
		$content = str_replace('template="Magento_Newsletter::subscribe.phtml"','template="MGS_Amp::Magento_Newsletter/subscribe.phtml"',$content);
		
		/* Brand Block */
		$content = str_replace('template="widget/home.phtml"','template="MGS_Amp::MGS_Brand/home.phtml"',$content);
		
		/* Blog Post Block */
		$content = str_replace('template="widget/default.phtml"','template="MGS_Amp::MGS_Blog/default.phtml"',$content);
		$content = str_replace('template="widget/list.phtml"','template="MGS_Amp::MGS_Blog/default.phtml"',$content);
		
		/* Testimonial Block */
		$content = str_replace('template_mode="center_content" template="grid.phtml"','template_mode="center_content" template="MGS_Amp::MGS_Testimonial/grid.phtml"',$content);
		$content = str_replace('template_mode="default_template" template="grid.phtml"','template_mode="default_template" template="MGS_Amp::MGS_Testimonial/grid.phtml"',$content);
		$content = str_replace('template_mode="boxed_content" template="grid.phtml"','template_mode="boxed_content" template="MGS_Amp::MGS_Testimonial/grid.phtml"',$content);
		
		/* Portfolio Block */
		$content = str_replace('template="widget/grid.phtml"','template="MGS_Amp::MGS_Portfolio/grid.phtml"',$content);
		
		/* Remove other block */
		$content = str_replace('template="widget/fanbox.phtml"','template="MGS_Amp::blank.phtml"',$content);
		$content = str_replace('template="widget/twitter_feed.phtml"','template="MGS_Amp::blank.phtml"',$content);
		$content = str_replace('template="MGS_Lookbook::widget/lookbook.phtml"','template="MGS_Amp::blank.phtml"',$content);
		$content = str_replace('template="MGS_Lookbook::widget/slider.phtml"','template="MGS_Amp::blank.phtml"',$content);
		$content = str_replace('template="widget/snapppt_shop.phtml"','template="MGS_Amp::blank.phtml"',$content);
		
		return $content;
	}
}