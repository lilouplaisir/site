<?php

/*******************************************************
 * Copyright (C) 2018 La Poste.
 *
 * This file is part of La Poste - Colissimo module.
 *
 * La Poste - Colissimo module can not be copied and/or distributed without the express
 * permission of La Poste.
 *******************************************************/

namespace LaPoste\Colissimo\Setup;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;
use LaPoste\Colissimo\Api\ColissimoStatus;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Eav\Setup\EavSetupFactory;


class UpgradeData implements UpgradeDataInterface
{
    protected $statusFactory;

    protected $statusResourceFactory;

    protected $salesSetupFactory;

    protected $eavSetupFactory;

    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory,
        SalesSetupFactory $salesSetupFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $this->addNewOrderCompletedStatus();
        $this->addRelayIdOrder($setup);
        $this->addHSCodeAttribute($setup, $context);
        $setup->endSetup();
    }

    protected function addNewOrderCompletedStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();

        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => ColissimoStatus::ORDER_STATUS_TRANSIT,
            'label' => 'Colissimo In-Transit',
        ]);

        try {
            $statusResource->save($status);
            $status->assignState(Order::STATE_COMPLETE, false, true);
        } catch (AlreadyExistsException $exception) {
            // do nothing more
        }

        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => ColissimoStatus::ORDER_STATUS_DELIVERED,
            'label' => 'Colissimo Delivered',
        ]);

        try {
            $statusResource->save($status);
            $status->assignState(Order::STATE_COMPLETE, false, true);
        } catch (AlreadyExistsException $exception) {
            // do nothing more
        }

        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => ColissimoStatus::ORDER_STATUS_ANOMALY,
            'label' => 'Colissimo Anomaly',
        ]);

        try {
            $statusResource->save($status);
            $status->assignState(Order::STATE_COMPLETE, false, true);
        } catch (AlreadyExistsException $exception) {
            // do nothing more
        }

        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => ColissimoStatus::ORDER_STATUS_READYTOSHIP,
            'label' => 'Colissimo Ready to ship',
        ]);

        try {
            $statusResource->save($status);
            $status->assignState(Order::STATE_PROCESSING, false, true);
        } catch (AlreadyExistsException $exception) {
            // do nothing more
        }
    }


    protected function addRelayIdOrder($setup)
    {
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        if (!$salesSetup->getAttributeId(Order::ENTITY, 'lpc_relay_id')) {
            $salesSetup->addAttribute(
                Order::ENTITY,
                'lpc_relay_id',
                [
                    'type' => 'varchar',
                    'label' => 'ID du point relais Colissimo',
                    'required' => false,
                    'visible' => false,
                ]
            );
        }

        if (!$salesSetup->getAttributeId(Order::ENTITY, 'lpc_relay_type')) {
            $salesSetup->addAttribute(
                Order::ENTITY,
                'lpc_relay_type',
                [
                    'type' => 'varchar',
                    'label' => 'Type de relais Colissimo',
                    'required' => false,
                    'visible' => false,
                ]
            );
        }
    }

    /**
     * Add product attribute to set specific HS code per product
     * @param $setup
     * @param $context
     */
    protected function addHSCodeAttribute($setup, $context)
    {
        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'lpc_hs_code')) {
                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'lpc_hs_code',
                    [
                        'type' => 'int',
                        'backend' => '',
                        'frontend' => '',
                        'label' => 'Product HS Code',
                        'input' => 'text',
                        'class' => '',
                        'source' => '',
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => false,
                        'default' => '',
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'used_in_product_listing' => false,
                        'unique' => false,
                        'apply_to' => '',
                    ]
                );
            }
        }
    }
}
