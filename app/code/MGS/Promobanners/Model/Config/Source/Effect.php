<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\Promobanners\Model\Config\Source;

class Effect
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			''=> __('No Effect'),
			'zoom' => __('Effect 1'),
			'border-zoom' => __('Effect 2'),
			'flashed' => __('Effect 3'),
			'zoom-flashed' => __('Effect 4'),
			'shadow-corner' => __('Effect 5'),
			'zoom-shadow' => __('Effect 6'),
			'cup-border' => __('Effect 7'),
			'flashed-zoom' => __('Effect 8'),
			'zoom-out-shadow' => __('Effect 9'),
			'mist' => __('Effect 10'),
			'mist-text' => __('Effect 11'),
		];
    }
}
