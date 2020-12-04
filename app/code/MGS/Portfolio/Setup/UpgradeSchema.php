<?php
namespace MGS\Portfolio\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup,
                            ModuleContextInterface $context){

        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'mgs_portfolio_item_store'
         */
        if(version_compare($context->getVersion(), '1.0.0.2', '<=')){
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mgs_portfolio_item_store')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Portfolio Store Id'
            )->addColumn(
                'portfolio_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Portfolio Id'
            )->addColumn(
                'store_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Store Id'
            );

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}