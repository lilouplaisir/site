<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Ui\Component\Listing;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Component\Listing\Columns as ListingColumns;

class Columns extends ListingColumns
{
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * Columns constructor.
     * @param ContextInterface $context
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        BookmarkManagementInterface $bookmarkManagement,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->bookmarkManagement = $bookmarkManagement;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        $this->prepareColumns();
    }

    /**
     * Prepare component columns configuration
     *
     * return void
     */
    private function prepareColumns()
    {
        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            'sales_order_grid'
        );
        $config = $bookmark ? $bookmark->getConfig() : null;
        $bookmarksCols = [];

        if (is_array($config) && isset($config['current']) && isset($config['current']['columns'])) {
            $bookmarksCols = $config['current']['columns'];
        }

        foreach ($this->getChildComponents() as $id => $column) {
            if ($column instanceof ListingColumns\Column) {
                $config = $column->getData('config');

                if (isset($bookmarksCols[$id], $bookmarksCols[$id]['amogrid_label'])) {
                    $config['amogrid_label'] = $bookmarksCols[$id]['amogrid_label'];
                    $config['default_label'] = $config['label'];
                    $config['label'] = $bookmarksCols[$id]['amogrid_label'];
                } elseif (isset($config['label'])) {
                    $config['amogrid_label'] = $config['default_label'] = $config['label'];
                }

                if (isset($bookmarksCols[$id], $bookmarksCols[$id]['visible'])) {
                    $config['visible'] = $bookmarksCols[$id]['visible'];
                } elseif (isset($config['visible'])) {
                    $config['visible'] = $config['visible'];
                }

                $column->setData('config', $config);
            }
        }
    }
}
