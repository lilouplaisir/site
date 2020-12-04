<?php

namespace MGS\Brand\Block\Brand\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $layer = $this->getLayer();
            $brand = $this->_coreRegistry->registry('current_brand');
            if ($brand) {
                $layer->setCurrentBrand($brand);
            }
            $collection = $layer->getProductCollection();

			if(!$this->getRequest()->getParam('product_list_order')){
				$order = 'ASC';
				if($this->getRequest()->getParam('product_list_dir')){
					$order = $this->getRequest()->getParam('product_list_dir');
				}
				$joinConditions = 'e.entity_id = brand_product.product_id';

				$collection->getSelect()->distinct('e.entity_id')->joinLeft(
					['brand_product' => $collection->getTable('mgs_brand_product')],
					$joinConditions,
					['pos'=>'brand_product.position']
				)->where('brand_product.brand_id = '.$this->getRequest()->getParam('brand_id')
				)->order('pos '.$order);
			}
			
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }
}