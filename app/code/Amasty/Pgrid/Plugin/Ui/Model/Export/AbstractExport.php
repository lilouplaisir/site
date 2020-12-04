<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


declare(strict_types=1);

namespace Amasty\Pgrid\Plugin\Ui\Model\Export;

use Magento\Catalog\Helper\Image;
use Magento\Framework\Api\Search;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Element;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export;

abstract class AbstractExport
{
    /**
     * @var string
     */
    protected $exportType = '';

    /**
     * @var int|null
     */
    protected $pageSize = null;

    /**
     * @var null
     */
    protected $sortedColumnMapping = null;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Filesystem\File\WriteInterface
     */
    protected $directory;

    /**
     * @var Export\MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var ExcelFactory
     */
    protected $excelFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Export\SearchResultIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var Element\UiComponent\Context
     */
    protected $context;

    /**
     * @var Search\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Search\SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var Image
     */
    protected $imageHelper;

    public function __construct(
        RequestInterface $request,
        Element\UiComponent\Context $context,
        Filesystem $filesystem,
        Filter $filter,
        Export\MetadataProvider $metadataProvider,
        ExcelFactory $excelFactory,
        Export\SearchResultIteratorFactory $iteratorFactory,
        Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterManager $filterManager,
        Random $random,
        Image $imageHelper,
        $pageSize = 200
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->excelFactory = $excelFactory;
        $this->iteratorFactory = $iteratorFactory;
        $this->pageSize = $pageSize;
        $this->request = $request;
        $this->context = $context;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterManager = $filterManager;
        $this->random = $random;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @return bool
     */
    public function checkNamespace(): bool
    {
        return $this->request->getParam('namespace') === 'product_listing';
    }

    /**
     * @param array $item
     * @return array
     */
    public function getRowXmlData($item): array
    {
        return $this->getRowData($item, $this->sortedColumnMapping);
    }

    /**
     * @param array $item
     * @param array $mapping
     * @return array
     */
    protected function getRowData(array $item, array &$mapping): array
    {
        $row = [];

        foreach ($mapping as $mappingField) {
            if (isset($item[$mappingField])) {
                if ($mappingField === 'thumbnail') {
                    /**
                     * Reinitialize $imageHelper on every datasource item
                     * @see \Magento\Catalog\Ui\Component\Listing\Columns\Thumbnail::prepareDataSource
                     */
                    $product = new \Magento\Framework\DataObject($item);
                    $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail');
                    $row[] = $imageHelper->getUrl();
                } else {
                    $row[] = is_array($item[$mappingField])
                        ? $this->filterManager->stripTags(implode(', ', $item[$mappingField]))
                        : $this->filterManager->stripTags($item[$mappingField]);
                }
            } else {
                $row[] = '-';
            }
        }

        return $row;
    }

    /**
     * @param Element\UiComponentInterface $component
     * @return array|null
     * @throws \Exception
     */
    protected function getSortedColumnFieldMapping(Element\UiComponentInterface $component)
    {
        if ($this->sortedColumnMapping === null) {
            foreach ($this->getColumnsComponent($component)->getChildComponents() as $column) {
                if ($column->getData('config/label')
                    && $column->getData('config/dataType') !== 'actions'
                    && $column->getData('config/ampgrid/visible') === true
                ) {
                    $this->sortedColumnMapping[$column->getName()] = $column->getData('config/sortOrder') ?? 99999;
                }
            }

            asort($this->sortedColumnMapping);
            $this->sortedColumnMapping = array_keys($this->sortedColumnMapping);
        }

        return $this->sortedColumnMapping;
    }

    /**
     * Get header mapping array with aliases as values
     *
     * @param Element\UiComponentInterface $component
     * @param array $sortedMapping
     * @return array
     */
    protected function getHeaders(Element\UiComponentInterface $component, array $sortedMapping): array
    {
        $headerFieldMapping = array_combine(
            $this->metadataProvider->getFields($component),
            $this->metadataProvider->getHeaders($component)
        );

        return array_map(function ($field) use ($headerFieldMapping) {
            return $headerFieldMapping[$field];
        }, $sortedMapping);
    }

    /**
     * @param Element\UiComponentInterface $component
     * @return Element\UiComponentInterface|Columns
     * @throws LocalizedException
     */
    private function getColumnsComponent(Element\UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $childComponent) {
            if ($childComponent instanceof Columns) {
                return $childComponent;
            }
        }

        throw new LocalizedException(__('No columns found'));
    }
}
