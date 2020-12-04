<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Cms;

/**
 * Main contact form block
 */
class Page extends \Magento\Cms\Block\Page
{	
	/**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;
	
    protected $_panelHelper;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Cms\Model\Page $page,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Framework\View\Page\Config $pageConfig,
        \MGS\Mpanel\Helper\Data $panelHelper,
        array $data = []
    ) {
        parent::__construct($context, $page, $filterProvider, $storeManager, $pageFactory, $pageConfig, $data);
		$this->_panelHelper = $panelHelper;
    }
	
	/**
     * Prepare HTML content
     *
     * @return string
     */
    protected function _toHtml()
    {
		$html = '';
		$canUsePanel = $this->_panelHelper->acceptToUsePanel();
		if($canUsePanel){
			$html .= '<span class="builder-container child-builder static-can-edit">
				<span class="edit-panel child-panel">
					<ul>
						<li><a title="'.__('Edit').'" class="popup-link" href="'.str_replace('https:','',str_replace('http:','',$this->getUrl('mpanel/edit/staticblock',['cms'=>'page', 'id'=>$this->getPage()->getIdentifier()]))).'"><em class="fa fa-edit">&nbsp;</em></a></li>
					</ul>
				</span>
				<span>';
		}
        $html .= $this->_filterProvider->getPageFilter()->filter($this->getPage()->getContent());
		
		if($canUsePanel){
			$html .= '</span></span>';
		}
        return $html;
    }
}

