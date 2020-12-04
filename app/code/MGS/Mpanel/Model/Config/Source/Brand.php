<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\Mpanel\Model\Config\Source;

class Brand implements \Magento\Framework\Option\ArrayInterface
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
			->addFieldToFilter('backend_type', 'int')
			->addFieldToFilter('frontend_input', 'select');
		
		if(count($attributes)>0){
			foreach ($attributes as $productAttr) { 
				$attrs[] = ['value'=>$productAttr->getAttributeCode(), 'label'=>$productAttr->getFrontendLabel()];
			}
		}
		
        return $attrs;
    }
}
