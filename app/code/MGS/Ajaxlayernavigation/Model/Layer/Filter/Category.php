<?php
namespace MGS\Ajaxlayernavigation\Model\Layer\Filter; 

class Category extends \MGS\Ajaxlayernavigation\Model\Layer\Filter\DefaultFilter
{
    protected $appliyedFilter;
    
    protected $filterPlus;

    public function __construct(
        \MGS\Ajaxlayernavigation\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \MGS\Ajaxlayernavigation\Model\Layer\Filter\ItemBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->escaper = $escaper;
        $this->_requestVar = 'cat';
        $this->appliedFilter = [];
        $this->filterPlus = false;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $categoryId = $request->getParam($this->_requestVar) ?: $request->getParam('id');
        if (empty($categoryId)) {
            return $this;
        }

        $this->dataProvider->setCategoryId($categoryId);

        $category = $this->dataProvider->getCategory();

        $this->getLayer()->getProductCollection()->addCategoryFilter($category);

        if ($request->getParam('id') != $category->getId() && $this->dataProvider->isValid()) {
            $this->getLayer()->getState()->addFilter(
                $this->_createItem(
                    $category->getName(),
                    $categoryId
                )
            );
        }
        return $this;
    }

    protected function _getItemsData()
    {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $optionsFacetedData = $productCollection->getFacetedData('category');
        $category = $this->dataProvider->getCategory();
        $categories = $category->getChildrenCategories();

        $collectionSize = $productCollection->getSize();

        if ($category->getIsActive()) {
            foreach ($categories as $category) {
                if ($category->getIsActive()
                    && isset($optionsFacetedData[$category->getId()])
                    && $this->isOptionReducesResults($optionsFacetedData[$category->getId()]['count'], $collectionSize)
                ) {
                    $this->_itemBuilder->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getId(),
                        $optionsFacetedData[$category->getId()]['count'],
                        false,
                        false
                    );
                }
            }
        }
        return $this->_itemBuilder->build();
    }

    public function getName()
    {
        return __('Category');
    }
}
