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

class AnimateSlider implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => '1', 'label' => __('Fade')],
			['value' => '2', 'label' => __('Back Slide')],
			['value' => '3', 'label' => __('Go Down')],
			['value' => '3', 'label' => __('Slide Vertical')]
		];
    }
}
