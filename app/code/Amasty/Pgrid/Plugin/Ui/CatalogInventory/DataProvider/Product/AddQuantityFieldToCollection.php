<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


declare(strict_types=1);

namespace Amasty\Pgrid\Plugin\Ui\CatalogInventory\DataProvider\Product;

class AddQuantityFieldToCollection
{
    /**
     * @param \Magento\CatalogInventory\Ui\DataProvider\Product\AddQuantityFieldToCollection $object
     * @param \Closure $proceed
     * @param \Magento\Framework\Data\Collection $collection
     * @param $field
     * @param null $alias
     */
    public function aroundAddField(
        \Magento\CatalogInventory\Ui\DataProvider\Product\AddQuantityFieldToCollection $object,
        \Closure $proceed,
        \Magento\Framework\Data\Collection $collection,
        $field,
        $alias = null
    ) {
        $fromPart = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
        if (!isset($fromPart['at_qty'])) {
            $proceed($collection, $field, $alias);
        }
    }
}
