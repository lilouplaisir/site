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

class Ratio implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		return [
			['value' => 1, 'label' => __('1/1 Square')], 
			['value' => 2, 'label' => __('1/2 Portrait')], 
			['value' => 3, 'label' => __('2/3 Portrait')], 
			['value' => 4, 'label' => __('3/4 Portrait')], 
			['value' => 5, 'label' => __('2/1 Landscape')], 
			['value' => 6, 'label' => __('3/2 Landscape')],
			['value' => 7, 'label' => __('4/3 Landscape')]
		];
    }
}
