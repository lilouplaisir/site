<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


namespace Amasty\Pgrid\Observer;

use Magento\Framework\Event\ObserverInterface;
use Amasty\Pgrid\Model\Indexer\QtySoldProcessor;
use Magento\Framework\Event\Observer;

class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var QtySoldProcessor
     */
    private $processor;

    public function __construct(QtySoldProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        if ($order && $order->getEntityId()) {
            $order->getResource()->addCommitCallback(function () use ($order) {
                $this->processor->reindexRow($order->getEntityId(), true);
            });
        }
    }
}
