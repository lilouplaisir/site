<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

declare(strict_types=1);

namespace Amasty\Pgrid\Ui\DataProvider\Product;

use Amasty\Pgrid\Model\Config\Source\Thumbnail;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class AddThumbnailFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    public function __construct(
        MetadataPool $metadataPool,
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->metadataPool = $metadataPool;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (!isset($condition['eq'])) {
            return;
        }
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $condition = $condition['eq'] == Thumbnail::NOT_ADDED ? '=' : '<>';
        $thumbnailAttributeId = $this->getThumbnailAttributeId();

        $collection->getSelect()->joinLeft(
            ['vc' => $collection->getTable('catalog_product_entity_varchar')],
            'e.' . $linkField . ' = vc.' . $linkField
        )->where(
            'vc.attribute_id = ' . $thumbnailAttributeId
        )->where(
            'vc.value ' . $condition . ' \'no_selection\''
        );
    }

    protected function getThumbnailAttributeId(): int
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToSelect('attribute_id')
            ->addFieldToFilter('attribute_code', ['eq' => 'thumbnail']);

        return (int)$attributeCollection->getConnection()->fetchOne($attributeCollection->getSelect());
    }
}
