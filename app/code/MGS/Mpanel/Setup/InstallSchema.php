<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Mpanel\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
		
		/**
         * Create table 'mgs_theme_home_store'
         */
        $home_store = $installer->getConnection()->newTable(
            $installer->getTable('mgs_theme_home_store')
        )->addColumn(
            'home_store_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Store View Id'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            2,
            ['nullable' => true],
            'Status'
        );

        $installer->getConnection()->createTable($home_store);

        /**
         * Create table 'mgs_theme_home_blocks'
         */
        $home_blocks = $installer->getConnection()->newTable(
            $installer->getTable('mgs_theme_home_blocks')
        )->addColumn(
            'block_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Block Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Block Name'
        )->addColumn(
            'theme_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Theme Name'
        )->addColumn(
            'block_cols',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Block Columns'
        )->addColumn(
            'tablet_cols',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Table columns'
        )->addColumn(
            'mobile_cols',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Mobile columns'
        )->addColumn(
            'block_class',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Block Class'
        )->addColumn(
            'class',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Class'
        )->addColumn(
            'background',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Section Background Color'
        )->addColumn(
            'background_image',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Section Background Image'
        )->addColumn(
            'background_repeat',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Background Repeat'
        )->addColumn(
            'background_cover',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Background Cover'
        )->addColumn(
            'parallax',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Background Parallax'
        )->addColumn(
            'fullwidth',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Section Full Width'
        )->addColumn(
            'padding_top',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Padding Top'
        )->addColumn(
            'padding_bottom',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Padding Bottom'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Store View Id'
        )->addColumn(
            'block_position',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Block Position'
        );

        $installer->getConnection()->createTable($home_blocks);
		
		/**
         * Create table 'mgs_theme_home_block_childs'
         */
        $home_block_childs = $installer->getConnection()->newTable(
            $installer->getTable('mgs_theme_home_block_childs')
        )->addColumn(
            'child_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'block_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Block Name'
        )->addColumn(
            'home_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Home Name'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Block Type'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Block Position'
        )->addColumn(
            'setting',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Block Setting'
        )->addColumn(
            'col',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Columns'
        )->addColumn(
            'class',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Block Class'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Store View Id'
        )->addColumn(
            'static_block_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Static Block Id'
        )->addColumn(
            'block_content',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Block Short Code'
        );

        $installer->getConnection()->createTable($home_block_childs);
		
		/**
         * Create table 'mgs_theme_catagory_setting'
         */
        $catagory_setting = $installer->getConnection()->newTable(
            $installer->getTable('mgs_theme_catagory_setting')
        )->addColumn(
            'category_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'ratio',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Product Picture Ratio'
        )->addColumn(
            'desc_position',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Description Position'
        )->addColumn(
            'number_product_on_row',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Number Of Poduct On A Row'
        );

        $installer->getConnection()->createTable($catagory_setting);
		

        $installer->endSetup();

    }
}
