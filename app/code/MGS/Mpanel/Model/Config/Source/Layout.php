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

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => '', 'label' => __('No Layout Update')], 
			['value' => '1column', 'label' => __('1 column')], 
			['value' => '2columns-left', 'label' => __('2 columns with left bar')], 
			['value' => '2columns-right', 'label' => __('2 columns with right bar')], 
			['value' => '3columns', 'label' => __('3 columns')], 
			['value' => 'empty', 'label' => __('Empty')]
		];
    }
}
