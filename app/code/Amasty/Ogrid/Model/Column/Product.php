<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Model\Column;

use Amasty\Base\Model\Serializer;
use Magento\Framework\DB\Helper;
use Magento\Framework\Module\Manager;
use Amasty\Ogrid\Model\Column;

class Product extends Column
{
    protected $_alias_prefix = 'amasty_ogrid_product_';

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        $fieldKey,
        $resourceModel,
        Serializer $serializer,
        Helper $dbHelper,
        Manager $moduleManager,
        $columns = [],
        $primaryKey = 'entity_id',
        $foreignKey = 'entity_id'
    ) {
        $this->moduleManager = $moduleManager;
        parent::__construct($fieldKey, $resourceModel, $serializer, $dbHelper, $columns, $primaryKey, $foreignKey);
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     */
    public function addFieldToSelect($collection)
    {
        $fromPart = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
        
        if ($this->_fieldKey == 'qty_available' && !isset($fromPart['amasty_stock_item_table'])) {
            $this->addStockTableToCollection($collection, 'amasty_stock_item_table');
        } else {
            $collection->getSelect()->columns([
                $this->_alias_prefix . $this->_fieldKey => $this->_fieldKey
            ]);
        }

        foreach ($this->_columns as $column) {
            $collection->getSelect()->columns([
                $this->_alias_prefix . $column => $column
            ]);
        }
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $orderItemCollection
     * @param mixed $value
     */
    public function addFieldToFilter($orderItemCollection, $value)
    {
        if (is_array($value)
            && array_key_exists('from', $value)
            && array_key_exists('to', $value)
        ) {
            $orderItemCollection->addFieldToFilter('main_table.' . $this->_fieldKey, [
                'between' => $value
            ]);
        } else {
            $orderItemCollection->addFieldToFilter('main_table.' . $this->_fieldKey, [
                'like' => '%'. $value . '%'
            ]);
        }
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $orderItemCollection
     * @param string $alias
     */
    private function addStockTableToCollection($orderItemCollection, $alias)
    {
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $orderItemCollection->getSelect()->joinLeft(
                [$alias => $orderItemCollection->getTable('inventory_source_item')],
                $alias . '.sku = main_table.sku AND ' . $alias . '.status = 1',
                ['main_table.item_id', 'SUM(' . $alias . '.quantity) AS ' . $this->_alias_prefix . $this->_fieldKey]
            )->group('main_table.item_id');
        } else {
            $orderItemCollection->getSelect()->joinLeft(
                [$alias => $orderItemCollection->getTable('cataloginventory_stock_item')],
                $alias . '.product_id = main_table.product_id',
                $alias . '.qty AS ' . $this->_alias_prefix . $this->_fieldKey
            );
        }
    }
}
