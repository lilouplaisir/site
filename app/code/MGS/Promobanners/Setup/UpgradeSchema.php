<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Promobanners\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableAdmins = $installer->getTable('promobanners');

        $installer->getConnection()->addColumn(
            $tableAdmins,
            'identifier',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
                'nullable' => true,
                'comment' => 'Identifier'
            ]
        );
        $installer->endSetup();
    }
}
