<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


declare(strict_types=1);

namespace Amasty\Pgrid\Ui\Component\Listing\Column;

use Amasty\Pgrid\Helper\Data;
use Magento\Catalog\Model\Product\LinkFactory as ProductLinkFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as LinkCollectionFactory;
use Magento\Framework\Escaper;

class LinkedProducts
{
    /**
     * @var LinkCollectionFactory
     */
    private $linkCollectionFactory;

    /**
     * @var ProductLinkFactory
     */
    protected $productLinkFactory;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        LinkCollectionFactory $linkCollectionFactory,
        ProductLinkFactory $productLinkFactory,
        Data $data,
        Escaper $escaper
    ) {
        $this->linkCollectionFactory = $linkCollectionFactory;
        $this->productLinkFactory = $productLinkFactory;
        $this->data = $data;
        $this->escaper = $escaper;
    }

    public function getLinkedProductsHtml(array $productIds, int $linkTypeId, string $column, array &$result): void
    {
        if (!$productIds) {
            return;
        }
        $idNameMapping = [];
        $linkModel = $this->productLinkFactory->create()->setLinkTypeId($linkTypeId);
        $collection = $this->linkCollectionFactory->create()
            ->addProductFilter($productIds)
            ->setVisibility([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH])
            ->setLinkModel($linkModel)
            ->setPositionOrder()
            ->setGroupBy();
        $collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'left');
        $qty = (int)$this->data->getModuleConfig('extra_columns/product_settings/products_qty');
        foreach ($collection as $item) {
            if (!empty($idNameMapping[$item->getData('_linked_to_product_id')])
                && count($idNameMapping[$item->getData('_linked_to_product_id')]) >= $qty
            ) {
                continue;
            }
            $idNameMapping[$item->getData('_linked_to_product_id')][] =
                '<div style="margin-bottom: 8px; border-bottom: 1px dotted #bcbcbc;">'
                . $this->escaper->escapeHtml($item->getname()) . '</div>';
        }
        foreach ($result['items'] as $key => $item) {
            if (array_key_exists($item['entity_id'], $idNameMapping)) {
                $result['items'][$key][$column] = implode('', $idNameMapping[$item['entity_id']]);
            }
        }
    }
}
