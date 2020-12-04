<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


declare(strict_types=1);

namespace Amasty\Ogrid\Model\Export;

use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToXml as ExportConvertToXml;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;

class ConvertToXml extends ExportConvertToXml
{
    use ExportTrait;

    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var Random
     */
    private $random;

    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        ExcelFactory $excelFactory,
        SearchResultIteratorFactory $iteratorFactory,
        BookmarkManagementInterface $bookmarkManagement,
        Random $random
    ) {
        parent::__construct($filesystem, $filter, $metadataProvider, $excelFactory, $iteratorFactory);
        $this->bookmarkManagement = $bookmarkManagement;
        $this->random = $random;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getXmlFile()
    {
        $component = $this->filter->getComponent();
        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            'sales_order_grid'
        );
        $config = $bookmark ? $bookmark->getConfig() : null;
        $bookmarksCols = [];
        $availableProductDetails = [];

        if (is_array($config) && isset($config['current']['columns'])) {
            $bookmarksCols = $config['current']['columns'];
        }

        foreach ($bookmarksCols as $key => $colItem) {
            if (!empty($colItem['visible'])
                && $colItem['visible'] == true
                && stripos($key, 'amasty_ogrid_product') !== false
            ) {
                $availableProductDetails[$key] = $colItem['amogrid_label'] ?? $key;
            }
        }
        
        $name = $this->random->getUniqueHash();
        $file = 'export/'. $name . '.xml';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $component->getContext()->getDataProvider()->setLimit(0, 0);

        /** @var \Magento\Framework\Api\Search\DocumentInterface[] $searchResultItems */
        $searchResultItems = $this->getDataProviderItems(
            $component->getContext()->getDataProvider(),
            $availableProductDetails
        );

        $this->prepareItems($component->getName(), $searchResultItems);

        /** @var \Magento\Ui\Model\Export\SearchResultIterator $searchResultIterator */
        $searchResultIterator = $this->iteratorFactory->create(['items' => $searchResultItems]);

        /** @var \Magento\Framework\Convert\Excel $excel */
        $excel = $this->excelFactory->create([
            'iterator' => $searchResultIterator,
            'rowCallback'=> [$this, 'getRowData'],
        ]);

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $excel->setDataHeader($this->metadataProvider->getHeaders($component));
        $excel->write($stream, $component->getName() . '.xml');

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }
}
