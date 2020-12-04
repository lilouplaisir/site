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

class Hover implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => '', 'label' => __('Effect 1')], 
			['value' => 'template-2', 'label' => __('Effect 2')], 
			['value' => 'template-3', 'label' => __('Effect 3')] 
		];
    }
}
