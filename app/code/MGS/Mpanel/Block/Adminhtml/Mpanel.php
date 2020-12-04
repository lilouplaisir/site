<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Adminhtml;

class Mpanel extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_mpanel';
        $this->_blockGroup = 'MGS_Mpanel';
        $this->_headerText = __('Mpanel');
        $this->_addButtonLabel = __('Add Item');
        parent::_construct();
    }

}
