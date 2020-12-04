<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Mpanel\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class InstallData implements InstallDataInterface
{
	/**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
    
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
	
	private $eavSetupFactory; 

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory, CustomerSetupFactory $customerSetupFactory, AttributeSetFactory $attributeSetFactory)
    {
		$this->eavSetupFactory = $eavSetupFactory;
		$this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
		/* Create thumbnail attribute for category */
        /** @var EavSetup $eavSetup */
        $categorySetup = $this->eavSetupFactory->create(['setup' => $setup]);
		$entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
			'mgs_thumbnail', 
			[
				'type' => 'varchar',
				'label' => 'Thumbnail Image',
				'input' => 'image',
				'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
				'required' => false,
				'sort_order' => 1,
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
				'group' => 'Content',
			]
        );
		
		$categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
			'per_row', 
			[
				'type' => 'varchar',
				'label' => 'Product Per Row',
				'input' => 'select',
				'source' => 'MGS\Mpanel\Model\Category\Attribute\Source\Perrow',
				'required' => false,
				'sort_order' => 100,
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'group' => 'Display Settings',
			]
        );
		
		$categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
			'picture_ratio', 
			[
				'type' => 'varchar',
				'label' => 'Product Picture Ratio',
				'input' => 'select',
				'source' => 'MGS\Mpanel\Model\Category\Attribute\Source\Ratio',
				'required' => false,
				'sort_order' => 110,
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'group' => 'Display Settings',
			]
        );
		
		/* Create customer attribute is_builder_account for front-end builder*/
		/** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        
        $customerSetup->addAttribute(Customer::ENTITY, 'is_builder_account', [
            'type' => 'int',
            'label' => 'Is Front-end Builder Account',
            'input' => 'select',
            'required' => false,
            'visible' => true,
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'user_defined' => true,
            'sort_order' => 1000,
            'is_used_in_grid' => 1,
            'is_visible_in_grid' => 1,
            'is_filterable_in_grid' => 1,
            'is_searchable_in_grid' => 1,
            'position' => 1000,
            'default' => 0,
            'system' => 0,
        ]);
        
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_builder_account')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer'],
        ]);
        
        $attribute->save();
		
		/* Create product attribute lay_out_tempalte*/
		/** @var CustomerSetup $customerSetup */
		$productSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $productTypes = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
        ];
        $productTypes = join(',', $productTypes);
        $productSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mgs_detail_template',
            [
				'group' => 'Design',
                'sort_order' => 1000,
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Template Layout',
                'input' => 'select',
                'class' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'source' => 'MGS\Mpanel\Model\Entity\Attribute\Backend\TemplateDetail',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_in_advanced_search' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => $productTypes,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );
    }
}