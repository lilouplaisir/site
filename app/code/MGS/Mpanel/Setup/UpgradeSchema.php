<?php
/**
* Copyright © 2016 MGS-THEMES. All rights reserved.
*/

namespace MGS\Mpanel\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.9', '<=')) {
			$connection = $setup->getConnection();
			
			$homeBlockTable = $setup->getTable('mgs_theme_home_blocks');
			$childTable = $setup->getTable('mgs_theme_home_block_childs');
			
            if ($connection->isTableExists($homeBlockTable) == true) {
				$connection->addColumn($homeBlockTable, 'page_id', [
					'type' => Table::TYPE_INTEGER,
					'nullable' => true,
					'after' => 'block_id',
					'comment' => 'CMS Page Id'
				]);

			}
			
			if ($connection->isTableExists($childTable) == true) {
                $connection->addColumn($childTable, 'page_id', [
					'type' => Table::TYPE_INTEGER,
					'nullable' => true,
					'after' => 'child_id',
					'comment' => 'CMS Page Id'
				]);
			}
		}
		
        $setup->endSetup();
    }
}