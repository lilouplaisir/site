<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Products\Category;

/**
 * Main contact form block
 */
class Tabs extends \Magento\Catalog\Block\Product\AbstractProduct
{
	/**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
	
	/**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
	
	/**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
	protected $_count;
    /**
     * @param Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
		\Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
		$this->_objectManager = $objectManager;
        $this->httpContext = $httpContext;
		$this->urlHelper = $urlHelper;
		$this->formKey = $formKey;
        parent::__construct(
            $context,
            $data
        );
    }
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
	
	/**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getProductCollection($category)
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		
		$collection->addCategoryFilter($category);

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->addAttributeToSort('created_at', 'desc');
		
		//$this->_count = $collection->count();
		
        $collection->setPageSize($this->getProductsCount())
            ->setCurPage($this->getCurrentPage());
        return $collection;
    }
	
	public function getAllProductCount(){
		//return $this->_count;
	}
	
	/**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (!$this->hasData('products_count')) {
            return parent::getProductsCount();
        }
        return $this->getData('products_count');
    }
	
	public function getCurrentPage(){
		if ($this->getCurPage()) {
            return $this->getCurPage();
        }
		return 1;
	}
	
	public function getProductsPerRow(){
		if ($this->hasData('per_row')) {
            return $this->getData('per_row');
        }
		return false;
	}
	
	public function getCategoryByIds(){
		$result = [];
		if($this->hasData('category_ids')){
			$categoryIds = $this->getData('category_ids');
			$categoryArray = explode(',',$categoryIds);
			if(count($categoryArray)>0){
				foreach($categoryArray as $categoryId){
					$category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
					if($category->getId()){
						$result[] = $category;
					}
					
				}
			}
		}
		return $result;
	}
	
	public function getCustomClass(){
		if ($this->hasData('custom_class')) {
            return $this->getData('custom_class');
        }
	}
	
	public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
	
	/**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}

