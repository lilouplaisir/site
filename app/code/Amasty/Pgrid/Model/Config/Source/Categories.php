<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


namespace Amasty\Pgrid\Model\Config\Source;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\Data\CategoryTreeInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Data\OptionSourceInterface;
use Psr\Log\LoggerInterface;

class Categories implements OptionSourceInterface
{
    /**
     * @var CategoryManagementInterface
     */
    private $categoryManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $categoryPath;

    public function __construct(
        CategoryManagementInterface $categoryManagement,
        LoggerInterface $logger
    ) {
        $this->categoryManagement = $categoryManagement;
        $this->logger = $logger;
    }

    public function toOptionArray(): array
    {
        $optionArray = [];
        $arr = $this->toArray();
        $arr['no_category'] = __('No Categories');
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $optionArray;
    }

    public function toArray(): array
    {
        try {
            $categoryTreeList = $this->categoryManagement->getTree();
        } catch (\Exception $exception) {
            $this->logger->error(
                __('Something went wrong with getting category tree. Error: %2'),
                [$exception->getMessage()]
            );
            return [];
        }

        $this->buildPath($categoryTreeList);
        $options = [];
        foreach ($this->categoryPath as $i => $path) {
            $string = str_repeat(". ", max(0, ($path['level'] - 1) * 3)) . $path['name'];
            $options[$path['id']] = $string;
        }

        return $options;
    }

    protected function buildPath(CategoryTreeInterface $category): void
    {
        if ($category->getName()) {
            $this->categoryPath[] = [
                'id'    => $category->getId(),
                'level' => $category->getLevel(),
                'name'  => $category->getName(),
            ];
        }
        if ($category->getChildrenData()) {
            foreach ($category->getChildrenData() as $child) {
                $this->buildPath($child);
            }
        }
    }
}
