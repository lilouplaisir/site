<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


declare(strict_types=1);

namespace Amasty\Pgrid\Observer;

use Amasty\Pgrid\Model\Indexer\QtySold;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var QtySold
     */
    private $indexBuilder;

    public function __construct(QtySold $indexBuilder)
    {
        $this->indexBuilder = $indexBuilder;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getData('product');

        if (!$product->getOrigData('entity_id')) {
            $this->indexBuilder->addEmptyIndexByProductIds($product->getId());
        }
    }
}
