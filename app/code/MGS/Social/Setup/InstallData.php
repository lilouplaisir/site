<?php

namespace MGS\Social\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'mgs_social_fid',
            [
                'type' => 'text',
                'visible' => false,
                'required' => false,
                'user_defined' => true,
            ]
        );
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'mgs_social_ftoken',
            [
                'type' => 'text',
                'visible' => false,
                'required' => false,
                'user_defined' => true,
            ]
        );
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'mgs_social_gid',
            [
                'type' => 'text',
                'visible' => false,
                'required' => false,
                'user_defined' => true,
            ]
        );
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'mgs_social_gtoken',
            [
                'type' => 'text',
                'visible' => false,
                'required' => false,
                'user_defined' => true,
            ]
        );
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'mgs_social_tid',
            [
                'type' => 'text',
                'visible' => false,
                'required' => false,
                'user_defined' => true,
            ]
        );
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'mgs_social_ttoken',
            [
                'type' => 'text',
                'visible' => false,
                'required' => false,
                'user_defined' => true,
            ]
        );
    }
}