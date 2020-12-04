<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


declare(strict_types=1);

namespace Amasty\Ogrid\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

trait ExportTrait
{
    /**
     * @param DataProviderInterface $dataProvider
     * @param array                 $availableProductDetails
     *
     * @return DocumentInterface[]
     */
    public function getDataProviderItems(DataProviderInterface $dataProvider, array $availableProductDetails)
    {
        $items = $dataProvider->getSearchResult()->getItems();
        $data = $dataProvider->getData();
        $dataItems = array_key_exists('items', $data) ? $data['items'] : [];

        foreach ($items as $idx => $item) {
            foreach ($dataItems as $dataItem) {
                if ($dataItem['entity_id'] == $item['entity_id']) {
                    if (array_key_exists('amasty_ogrid_items_ordered', $dataItem)) {
                        $dataItem['amasty_ogrid_items_ordered'] = $this->getOrderedItemsData(
                            $dataItem['amasty_ogrid_items_ordered'],
                            $availableProductDetails
                        );
                    }

                    if (array_key_exists('amasty_ogrid_sales_shipment_track', $dataItem)) {
                        $dataItem['amasty_ogrid_sales_shipment_track'] = implode(
                            ',',
                            $dataItem['amasty_ogrid_sales_shipment_track']
                        );
                    }

                    if (array_key_exists('amasty_perm_dealers', $dataItem)) {
                        $dataItem['amasty_perm_dealers'] = implode(',', $dataItem['amasty_perm_dealers']);
                    }

                    $item->setData($dataItem);

                    break;
                }
            }
        }

        return $items;
    }

    /**
     * @param array $orderedItems
     * @param array $availableProductDetails
     *
     * @return string
     */
    private function getOrderedItemsData(array $orderedItems, array $availableProductDetails): string
    {
        $productDetailsColumn = [];

        foreach ($orderedItems as $productInfo) {
            foreach ($productInfo as $key => $value) {
                if (array_key_exists($key, $availableProductDetails) && $value && !is_array($value)) {
                    $productDetailsColumn[] = $availableProductDetails[$key] . ':' . $value;
                }
            }
        }

        return implode('|', $productDetailsColumn);
    }
}
