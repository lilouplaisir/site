<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conditions
 */


namespace Amasty\Conditions\Setup;

use Amasty\Conditions\Setup\Operation\AddConditionsQuoteTable;
use Amasty\Conditions\Setup\Operation\ChangeColumnDefinition;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade Schema scripts
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var AddConditionsQuoteTable
     */
    private $addConditionsQuoteTable;

    /**
     * @var ChangeColumnDefinition
     */
    private $changeColumnDefinition;

    public function __construct(
        AddConditionsQuoteTable $addConditionsQuoteTable,
        ChangeColumnDefinition $changeColumnDefinition
    ) {
        $this->addConditionsQuoteTable = $addConditionsQuoteTable;
        $this->changeColumnDefinition = $changeColumnDefinition;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.4.1', '<')) {
            $this->addConditionsQuoteTable->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.4.2', '<')) {
            $this->changeColumnDefinition->execute($setup);
        }
    }
}
