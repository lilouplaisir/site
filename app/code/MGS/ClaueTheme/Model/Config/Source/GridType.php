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

class GridType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		return [
			['value' => 1, 'label' => __('Hover Effect 1')], 
			['value' => 2, 'label' => __('Hover Effect 2')], 
			['value' => 3, 'label' => __('Hover Effect 3')], 
			['value' => 4, 'label' => __('Hover Effect 4')], 
			['value' => 5, 'label' => __('Disable Hover Effect')], 
		];
    }
}
