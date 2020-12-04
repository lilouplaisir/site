<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


declare(strict_types=1);

namespace Amasty\Ogrid\Model\Export;

use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToCsv as ExportConvertToCsv;
use Magento\Ui\Model\Export\MetadataProvider;

class ConvertToCsv extends ExportConvertToCsv
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
        Filter $filter,
        MetadataProvider $metadataProvider,
        BookmarkManagementInterface $bookmarkManagement,
        Filesystem $filesystem,
        Random $random
    ) {
        parent::__construct($filesystem, $filter, $metadataProvider);
        $this->bookmarkManagement = $bookmarkManagement;
        $this->random = $random;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCsvFile()
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
        $file = 'export/'. $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
        $totalCount = (int)$dataProvider->getSearchResult()->getTotalCount();

        while ($totalCount > 0) {
            $items = $this->getDataProviderItems($dataProvider, $availableProductDetails);

            foreach ($items as $idx => $item) {
                $this->metadataProvider->convertDate($item, $component->getName());
                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
            }

            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }
}
