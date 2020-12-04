<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\ClaueTheme\Model\Config\Source;

class AttributeShow implements \Magento\Framework\Option\ArrayInterface
{
	public function __construct(
		\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection
    ) {
		$this->_attributeCollection = $attributeCollection;
    }
	
	public function getAttributeCollection(){
		return $this->_attributeCollection->create()->addVisibleFilter();
	}
	
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		$attrs = [['value' => '', 'label' => '']];
		
		$attributes = $this->getAttributeCollection()
			->addFieldToFilter('frontend_input', array('neq' => 'boolean'))
			->addFieldToFilter('is_user_defined', array('eq' => '1'));
		
		if(count($attributes)>0){
			foreach ($attributes as $productAttr) { 
				$attrs[] = ['value'=>$productAttr->getAttributeCode(), 'label'=>$productAttr->getFrontendLabel()];
			}
		}
		
        return $attrs;
    }
}
