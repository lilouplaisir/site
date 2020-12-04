<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


namespace Amasty\Pgrid\Plugin\Catalog\Ui\DataProvider\Product;

use Amasty\Pgrid\Api\Data\QtySoldInterface;
use Amasty\Pgrid\Helper\Data;
use Amasty\Pgrid\Setup\Operation\CreateQtySoldTable;
use Amasty\Pgrid\Ui\Component\Listing\Column\Availability;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface as StockConfigurationInterface;
use Magento\Eav\Model\Entity as EavEntity;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Ui\Api\BookmarkManagementInterface;

class ProductDataProvider
{
    /**
     * @var array
     */
    protected $_columns = [
        'amasty_categories',
        'amasty_link',
        'amasty_availability',
        'created_at',
        'updated_at',
        'amasty_related_products',
        'amasty_up_sells',
        'amasty_cross_sells',
        'amasty_low_stock'
    ];

    /**
     * @var array
     */
    protected $visibleColumns = ['price', 'qty'];

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var BookmarkManagementInterface
     */
    protected $_bookmarkManagement;

    /**
     * @var Http
     */
    protected $_http;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var Availability
     */
    protected $availabilityColumn;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var array
     */
    private $currentProducts;

    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        UrlInterface $url,
        BookmarkManagementInterface $bookmarkManagement,
        Escaper $escaper,
        Data $helper,
        Http $http,
        Availability $availabilityColumn,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_url = $url;
        $this->_bookmarkManagement = $bookmarkManagement;
        $this->_helper = $helper;
        $this->_http = $http;
        $this->availabilityColumn = $availabilityColumn;
        $this->stockConfiguration = $stockConfiguration;

        $request = $this->_http->getParams();
        if (isset($request['data'])) {
            $data = json_decode($request['data'], true);
            if (isset($data['column'])) {
                $this->visibleColumns[] = $data['column'];
            }
        }
        $this->escaper = $escaper;
    }

    protected function getCategoriesHtml(Product $product, array $allCategories): string
    {
        $categoriesHtml = '';
        $productCategories = $product->getCategoryCollection();
        if ($productCategories) {
            foreach ($productCategories as $category) {
                $path = '';
                $pathInStore = $category->getPathInStore();
                $pathIds = array_reverse(explode(',', $pathInStore));
                foreach ($pathIds as $categoryId) {
                    if (isset($allCategories[$categoryId]) && $categoryName = $allCategories[$categoryId]->getName()) {
                        $path .= $this->escaper->escapeHtml($categoryName) . '/';
                    }
                }

                if ($path) {
                    $path = substr($path, 0, -1);
                    $path =
                        '<div style=" margin-bottom: 8px; border-bottom: 1px dotted #bcbcbc;">' . $path
                        . '</div>';
                }

                $categoriesHtml .= $path;
            }
        }

        return $categoriesHtml;
    }

    private function prepareColumns(array $columns): void
    {
        foreach ($columns as $key => $column) {
            if (isset($column['visible']) && $column['visible']) {
                $this->visibleColumns[] = $key;
            }
        }
    }

    protected function getVisibleColumns(): array
    {
        $bookmarks = $this->_bookmarkManagement->loadByNamespace('product_listing');

        /** @var \Magento\Ui\Api\Data\BookmarkInterface $bookmark */
        foreach ($bookmarks->getItems() as $bookmark) {
            if (isset($bookmark->getConfig()['current']['columns'])) {
                $columns = $bookmark->getConfig()['current']['columns'];
                $this->prepareColumns($columns);
            } elseif (isset($bookmark->getConfig()['views'][$bookmark->getIdentifier()]['data']['columns'])) {
                $columns = $bookmark->getConfig()['views'][$bookmark->getIdentifier()]['data']['columns'];
                $this->prepareColumns($columns);
            }
        }

        return array_unique($this->visibleColumns);
    }

    public function beforeGetData(\Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject): void
    {
        $visibleColumns = $this->getVisibleColumns();

        foreach ($visibleColumns as $column) {
            $subject->getCollection()->addFieldToSelect($column);
        }

        if (in_array('amasty_categories', $visibleColumns)
            || in_array('amasty_link', $visibleColumns)
        ) {
            $subject->getCollection()->addUrlRewrite();
        }

        if (in_array('amasty_availability', $visibleColumns)
            && !$subject->getCollection()->getFlag('amasty_instock_filter')
        ) {
            $this->addInventoryColumn(
                $subject->getCollection(),
                'amasty_availability',
                $this->availabilityColumn->getAvailableExpression()
            );
        }

        if (in_array('amasty_backorders', $visibleColumns)) {
            $this->addInventoryColumn($subject->getCollection(), 'amasty_backorders', 'backorders');
        }

        if (in_array('amasty_low_stock', $visibleColumns)) {
            $this->_addLowStock($subject->getCollection());
        }

        if (in_array('amasty_qty_sold', $visibleColumns)) {
            $this->addQtySoldColumn($subject->getCollection());
        }
    }

    private function addQtySoldColumn(Collection $collection): void
    {
        $collection->joinField(
            'amasty_qty_sold',
            CreateQtySoldTable::TABLE_NAME,
            QtySoldInterface::QTY_SOLD,
            QtySoldInterface::PRODUCT_ID . '=' . EavEntity::DEFAULT_ENTITY_ID_FIELD,
            null,
            'left'
        );
    }

    private function addInventoryColumn(Collection $collection, string $amastyColumnName, string $columnName): void
    {
        $collection->joinField(
            $amastyColumnName,
            'cataloginventory_stock_item',
            $columnName,
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );
    }

    protected function _addLowStock(Collection $collection): void
    {
        $configManageStock = $this->stockConfiguration->getManageStock();

        $globalNotifyStockQty = (float)$this->_helper->getScopeValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_NOTIFY_STOCK_QTY
        );

        $stockItemWhere = '({{table}}.low_stock_date is not null) '
            . " AND ( ({{table}}.use_config_manage_stock=1 AND {$configManageStock}=1)"
            . " AND {{table}}.qty < "
            . "IF(amasty_low_stock_item.`use_config_notify_stock_qty`,"
            . " {$globalNotifyStockQty}, {{table}}.notify_stock_qty)"
            . ' OR ({{table}}.use_config_manage_stock=0 AND {{table}}.manage_stock=1) )';

        $collection
            ->addAttributeToSelect('name', true)
            ->joinTable(
                ['amasty_low_stock_item' => 'cataloginventory_stock_item'],
                'product_id=entity_id',
                ['if(amasty_low_stock_item.item_id IS NULL, 0 , 1) as amasty_low_stock'],
                $stockItemWhere,
                'left'
            )
            ->setOrder('amasty_low_stock_item.low_stock_date');
    }

    protected function _initCategories(array &$result): void
    {
        $idx = 0;
        $allCategories = $this->categoryCollectionFactory->create()->addNameToResult()->getItems();
        foreach ($this->currentProducts as $product) {
            $amastyCategoriesHtml = '';
            if (isset($result['items']) && isset($result['items'][$idx])) {
                $amastyCategoriesHtml = $this->getCategoriesHtml($product, $allCategories);
            }

            $result['items'][$idx]['amasty_categories'] = $amastyCategoriesHtml;
            $idx++;
        }
    }

    protected function _initExtra(array &$row, string $column): void
    {
        switch ($column) {
            case "amasty_link":
                if (!empty($row['request_path'])) {
                    $row[$column] = $this->_url->getUrl('', ['_direct' => $row['request_path']]);
                } else {
                    $row[$column] = $this->_url->getUrl(
                        null,
                        ['_direct' => 'catalog/product/view/id/' . $row['entity_id']]
                    );
                }
                break;
        }
    }

    protected function _initRelatedProducts(string $column, array &$result): void
    {
        $idx = 0;

        foreach ($this->currentProducts as $product) {
            $ret = '';
            $linkedProductCollection = [];

            switch ($column) {
                case "amasty_related_products":
                    $linkedProductCollection = $product->getRelatedProductCollection();
                    break;
                case "amasty_up_sells":
                    $linkedProductCollection = $product->getUpSellProductCollection();
                    break;
                case "amasty_cross_sells":
                    $linkedProductCollection = $product->getCrossSellProductCollection();
                    break;
            }
            $qty = $this->_helper->getModuleConfig('extra_columns/product_settings/products_qty');
            $linkedProductCollection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'left');
            $linkedProductCollection->setPageSize($qty);
            if ($linkedProductCollection) {
                foreach ($linkedProductCollection as $linkedProduct) {
                    $ret .= '<div style="margin-bottom: 8px; border-bottom: 1px dotted #bcbcbc;">'
                        . $this->escaper->escapeHtml($linkedProduct->getName()) . '</div>';
                }
            }

            $result['items'][$idx][$column] = $ret;
            $idx++;
        }
    }

    private function initTierPrices(array &$result): void
    {
        $idx = 0;
        if (isset($result['items'])) {
            foreach ($this->currentProducts as $product) {
                if ($productTierPrices = $product->getTierPrices()) {
                    $result['items'][$idx]['amasty_tier_price'] = $this->getTierPriceHtml($productTierPrices);
                }
                $idx++;
            }
        }
    }

    private function getTierPriceHtml(array $productTierPrices): string
    {
        $tierPriceHtml = '';
        foreach ($productTierPrices as $tierPriceItem) {
            if ((int)$tierPriceItem['qty'] != 0 && (int)$tierPriceItem['value'] != 0) {
                $tierPriceHtml .= '<p style="width:130px;">' .
                    $this->escaper->escapeHtml(__('For Qty')) . ' = ' . round($tierPriceItem['qty'], 2) .
                    $this->escaper->escapeHtml(__(' Price')) . ' = ' . round($tierPriceItem['value'], 2)
                    . '</p>';
            }
        }

        return $tierPriceHtml;
    }

    public function afterGetData(
        \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject,
        $result
    ) {
        $columns = $this->getVisibleColumns();
        $this->currentProducts = $subject->getCollection()->getItems();

        foreach ($columns as $column) {
            switch ($column) {
                case "amasty_categories":
                    $this->_initCategories($result);
                    break;
                case "amasty_related_products":
                case "amasty_up_sells":
                case "amasty_cross_sells":
                    $this->_initRelatedProducts($column, $result);
                    break;
                case "price":
                    $this->processPriceColumn($result);
                    break;
                case "amasty_tier_price":
                    $this->initTierPrices($result);
                    break;
                case "qty":
                    $this->processQtyColumn($result);
                    break;
                default:
                    $this->processExtraColumn($result, $column);
                    break;
            }
        }

        return $result;
    }

    protected function processQtyColumn(array &$result): void
    {
        if (isset($result['items'])) {
            $showInteger = $this->_helper->getModuleConfig('modification/show_integer');
            foreach ($result['items'] as $idx => $item) {
                if (isset($item['qty'])) {
                    if ($showInteger) {
                        $result['items'][$idx]['qty'] = (int)$item['qty'];
                    }
                }
            }
        }
    }

    protected function processPriceColumn(array &$result): void
    {
        if (isset($result['items'])) {
            foreach ($result['items'] as $idx => $item) {
                if (isset($item['price'])) {
                    $result['items'][$idx]['amasty_price'] = $item['price'];
                }
            }
        }
    }

    protected function processExtraColumn(array &$result, string $column): void
    {
        if (isset($result['items'])) {
            foreach ($result['items'] as $idx => $item) {
                $this->_initExtra($result['items'][$idx], $column);
            }
        }
    }
}
