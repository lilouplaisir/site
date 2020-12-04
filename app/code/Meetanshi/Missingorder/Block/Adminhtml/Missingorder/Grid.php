<?php

namespace Meetanshi\Missingorder\Block\Adminhtml\Missingorder;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;

class Grid extends Extended
{
    private $registry;
    private $orderCollectionFactory;
    protected $quoteFactory;

    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $registry,
        QuoteCollection $quoteFactory,
        CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
    
        $this->registry = $registry;
        $this->quoteFactory = $quoteFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('Missingorderindex');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $quote = $this->quoteFactory->create()
            ->addFieldToSelect('*');

        $collection = $this->orderCollectionFactory->create()
            ->addFieldToSelect('increment_id');

        $arr = [];
        foreach ($collection as $orderID) {
            $arr[] = $orderID->getData('increment_id');
        }

        $quote->addFieldToFilter('reserved_order_id', ['neq' => '']);
        $quote->addFieldToFilter('reserved_order_id', ['nin' => $arr]);
        $quote->addFieldToFilter('items_qty', ['gt' => 0]);

        $this->setCollection($quote);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        try {
            $this->addColumn(
                'entity_id',
                [
                    'header' => __('ID'),
                    'align' => 'right',
                    'width' => '50px',
                    'index' => 'entity_id',
                ]
            );
            $this->addColumn(
                'reserved_order_id',
                [
                    'header' => __('Reserved Order ID'),
                    'align' => 'right',
                    'type' => 'text',
                    'index' => 'reserved_order_id',
                    'width' => '50px',
                ]
            );
            $this->addColumn(
                'customer_email',
                [
                    'header' => __('Customer Email'),
                    'align' => 'right',
                    'index' => 'customer_email',
                    'width' => '60px',
                ]
            );
            $this->addColumn(
                'created_at',
                [
                    'header' => __('Created At'),
                    'align' => 'right',
                    'width' => '50px',
                    'index' => 'created_at',
                    'type' => 'date',
                ]
            );
            $this->addColumn(
                'base_grand_total',
                [
                    'header' => __('G.T.(base)'),
                    'align' => 'right',
                    'index' => 'base_grand_total',
                    'type' => 'currency',
                    'width' => '50px',
                ]
            );

            $this->addColumn(
                'action',
                [
                    'header' => __('Action'),
                    'width' => '50px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => [
                        [
                            'caption' => __('Create Order'),
                            'url' => ['base' => 'missingorder/listing/create'],
                            'field' => 'id'
                        ]
                    ],
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'id',
                    'is_system' => true
                ]
            );
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
        return parent::_prepareColumns();
    }
}
