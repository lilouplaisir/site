<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Model\Entity\Attribute\Backend;

class TemplateDetail extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {return [
			['value' => '', 'label' => __('Use Default Config')],
			['value' => '0', 'label' => __('Product standard layout')], 
			['value' => '1', 'label' => __('Product gallery thumbnail')], 
			['value' => '2', 'label' => __('Product with sticky info')], 
			['value' => '3', 'label' => __('Product with sticky info 2')], 
			['value' => '4', 'label' => __('Product with vertical thumbnail')],
			['value' => '5', 'label' => __('Product slide gallery')]
		];
    }

}
