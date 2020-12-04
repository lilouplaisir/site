<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Amp\Block\Framework;

use Magento\Framework;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\View;

/**
 * Main contact form block
 */
class Page extends \MGS\Mpanel\Block\Framework\Page
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
    private $_ampHelper;

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
		\MGS\Amp\Helper\Config $ampHelper,
        $template,
        $isIsolated = false
    ) {
        $this->_ampHelper = $ampHelper;
        parent::__construct(
            $context,
            $layoutFactory,
            $layoutReaderPool,
            $translateInline,
            $layoutBuilderFactory,
            $generatorPool,
			$pageConfigRendererFactory,
			$pageLayoutReader,
			$builderHelper,
			$template,
            $isIsolated
        );
    }
	
	public function getAmpHelper(){
		return $this->_ampHelper;
	}
    
}

