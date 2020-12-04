<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\Landing\Model\Category\Attribute\Source;

class Template extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	
	
	public function getAllOptions()
    {
		$option = [
			['value' => 1, 'label' => __('Grid')],
			['value' => 2, 'label' => __('Masonry')], 
			['value' => 3, 'label' => __('Parallax')]
		];
        
        return $option;
    }
}
