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

class DtTemplate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => '0', 'label' => __('Product standard layout')], 
			['value' => '1', 'label' => __('Product gallery thumbnail')], 
			['value' => '2', 'label' => __('Product with sticky info')], 
			['value' => '3', 'label' => __('Product with sticky info 2')], 
            ['value' => '4', 'label' => __('Product with vertical thumbnail')],
			['value' => '5', 'label' => __('Product slide gallery')]
		];
    }
}
