<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\Mpanel\Model\Category\Attribute\Source;

class Ratio extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	
	public function getAllOptions()
    {
		$option = [
			['value' => '', 'label' => __('Use Config Setting')]
		];
		
		$ratioObj = new \MGS\Mpanel\Model\Config\Source\Ratio;
		$ratio = $ratioObj->toOptionArray();
		
		$option = array_merge($option, $ratio);
		
        return $option;
    }
}
