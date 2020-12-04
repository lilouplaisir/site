<?php

namespace MGS\Protabs\Block;

use Magento\Framework\Registry;


class DetailsProtabs extends \Magento\Catalog\Block\Product\View\Details{
    protected $_registry;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, array $data = [], Registry $registry)
    {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function getProduct(){
        $product = $this->_registry->registry('current_product');
        return $product;
    }
}