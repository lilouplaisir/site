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

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const DB_CN23_FLAG_COLUMN_NAME = 'lpc_label_cn_23';


    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->addColissimoEventStatus($setup);
        $this->addBordereauTable($setup);

        $setup->endSetup();
    }


    /**
     * Create table 'laposte_colissimo_bordereau'
     * @param $setup
     */
    protected function addBordereauTable($setup)
    {
        $tableName = $setup->getTable('laposte_colissimo_bordereau');
        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('laposte_colissimo_bordereau')
            )->addColumn(
                'entity_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Bordereau ID'
            )->addColumn(
                'bordereau_number',
                Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Numero du bordereau'
            )->addColumn(
                'code_site_pch',
                Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Code de site de prise en charge'
            )->addColumn(
                'number_of_parcels',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Nombre de colis'
            )->addColumn(
                'parcels_numbers',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Numeros des colis'
            )->addColumn(
                'publishing_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Date de generation du bordereau'
            )->setComment('LPC Bordereau Table');

            $setup->getConnection()->createTable($table);
        }
    }

    /**
     * Add scpecific Colissimo statuses
     * @param $setup
     */
    protected function addColissimoEventStatus($setup)
    {
        $salesShipmentTableName = $setup->getTable('sales_shipment');
        $databaseConnection = $setup->getConnection();

        $databaseConnection->addColumn(
            $salesShipmentTableName,
            "lpc_return_label",
            [
                "type" => Table::TYPE_VARBINARY,
                "length" => "2m",
                "comment" => "La Poste Colissimo return label"
            ]
        );
        $databaseConnection->addColumn(
            $salesShipmentTableName,
            self::DB_CN23_FLAG_COLUMN_NAME,
            [
                "type" => Table::TYPE_BOOLEAN,
                "comment" => "La Poste Colissimo using CN23"
            ]
        );

        $salesShipmentTrackTableName = $setup->getTable('sales_shipment_track');
        $databaseConnection->addColumn(
            $salesShipmentTrackTableName,
            "lpc_last_event_code",
            [
                "type" => Table::TYPE_TEXT,
                "length" => "255",
                "comment" => "La Poste Colissimo last event code"
            ]
        );
        $databaseConnection->addColumn(
            $salesShipmentTrackTableName,
            "lpc_last_event_date",
            [
                "type" => Table::TYPE_DATETIME,
                "comment" => "La Poste Colissimo last event date"
            ]
        );
        $databaseConnection->addColumn(
            $salesShipmentTrackTableName,
            "lpc_is_delivered",
            [
                "type" => Table::TYPE_BOOLEAN,
                "comment" => "La Poste Colissimo return label",
                "default" => '0'
            ]
        );
    }
}
