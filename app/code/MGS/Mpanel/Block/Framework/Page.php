<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Framework;

use Magento\Framework;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\View;

/**
 * Main contact form block
 */
class Page extends \Magento\Framework\View\Result\Page
{
	/**
     * @var string
     */
    protected $pageLayout;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Page\Config\RendererInterface
     */
    protected $pageConfigRenderer;

    /**
     * @var \Magento\Framework\View\Page\Config\RendererFactory
     */
    protected $pageConfigRendererFactory;

    /**
     * @var \Magento\Framework\View\Page\Layout\Reader
     */
    protected $pageLayoutReader;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var array
     */
    protected $viewVars;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var View\EntitySpecificHandlesList
     */
    private $entitySpecificHandlesList;
	
	/**
     * @var View\EntitySpecificHandlesList
     */
    private $_builderHelper;

    /**
     * Constructor
     *
     * @param View\Element\Template\Context $context
     * @param View\LayoutFactory $layoutFactory
     * @param View\Layout\ReaderPool $layoutReaderPool
     * @param Framework\Translate\InlineInterface $translateInline
     * @param View\Layout\BuilderFactory $layoutBuilderFactory
     * @param View\Layout\GeneratorPool $generatorPool
     * @param View\Page\Config\RendererFactory $pageConfigRendererFactory
     * @param View\Page\Layout\Reader $pageLayoutReader
     * @param string $template
     * @param bool $isIsolated
     * @param View\EntitySpecificHandlesList $entitySpecificHandlesList
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        View\Layout\ReaderPool $layoutReaderPool,
        Framework\Translate\InlineInterface $translateInline,
        View\Layout\BuilderFactory $layoutBuilderFactory,
        View\Layout\GeneratorPool $generatorPool,
        View\Page\Config\RendererFactory $pageConfigRendererFactory,
        View\Page\Layout\Reader $pageLayoutReader,
		\MGS\Mpanel\Helper\Data $builderHelper,
        $template,
        $isIsolated = false
    ) {
        $this->request = $context->getRequest();
        $this->assetRepo = $context->getAssetRepository();
        $this->logger = $context->getLogger();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->pageConfig = $context->getPageConfig();
        $this->pageLayoutReader = $pageLayoutReader;
        $this->viewFileSystem = $context->getViewFileSystem();
        $this->pageConfigRendererFactory = $pageConfigRendererFactory;
        $this->_builderHelper = $builderHelper;
        $this->template = $template;
        parent::__construct(
            $context,
            $layoutFactory,
            $layoutReaderPool,
            $translateInline,
            $layoutBuilderFactory,
            $generatorPool,
			$pageConfigRendererFactory,
			$pageLayoutReader,
			$template,
            $isIsolated
        );
        $this->initPageConfigReader();
    }
	
	public function getBuilderHelper(){
		return $this->_builderHelper;
	}
	
	/**
     * Add default body classes for current page layout
     *
     * @return $this
     */
    protected function addDefaultBodyClasses()
    {
        $this->pageConfig->addBodyClass($this->request->getFullActionName('-'));
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $this->pageConfig->addBodyClass('page-layout-' . $pageLayout);
        }
		$width = $this->getStoreConfig('mgstheme/general/width');
		if($width != 'width1200'){
			$this->pageConfig->addBodyClass($width);
		}
		$layout = $this->getStoreConfig('mgstheme/general/layout');
		$this->pageConfig->addBodyClass($layout);
		
		if($this->getStoreConfig('mgstheme/general/dark')){
			$this->pageConfig->addBodyClass('dark');
		}
        if($this->getStoreConfig('ajaxcart/additional/animation_type') == 'flycart'){
			$this->pageConfig->addBodyClass('flycart');
		}
		
        return $this;
    }
	
	public function getStoreConfig($node){
		$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\Mpanel\Helper\Data');
		
		return $helper->getStoreConfig($node);
	}
}

