<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


namespace Amasty\Pgrid\Ui\Component\Listing;

use Amasty\Base\Model\MagentoVersion;
use Amasty\Pgrid\Helper\Data;
use Amasty\Pgrid\Ui\Component\ColumnFactory;
use Amasty\Pgrid\Ui\Component\Listing\Attribute\Repository;
use Amasty\Pgrid\Ui\Component\Listing\Column\InlineEditUpdater;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    const DEFAULT_COLUMNS_MAX_ORDER = 100;
    const MAGENTO_INVENTORY_SALES_ADMIN_UI_MODULE_NAMESPACE = 'Magento_InventorySalesAdminUi';

    /**
     * @var Repository
     */
    protected $attributeRepository;

    /**
     * @var InlineEditUpdater
     */
    protected $inlineEditUpdater;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var BookmarkManagementInterface
     */
    protected $bookmarkManagement;

    /**
     * @var ColumnFactory
     */
    private $columnFactory;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var array
     */
    protected $filterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];

    /**
     * For column custom filters
     *
     * @var array
     */
    protected $forceFilter = [
        'amasty_categories'
    ];

    protected $skipAttributes = [
        'old_id',
        'tier_price',
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'page_layout',
        'category_ids',
        'options_container',
        'required_options',
        'has_options',
        'image_label',
        'small_image_label',
        'thumbnail_label',
        'created_at',
        'updated_at',
        'quantity_and_stock_status',
        'msrp',
        'msrp_display_actual_price_type',
        'price_view',
        'url_path',
        'weight_type',
//        'tax_class_id',
        'category_gear'
    ];

    public function __construct(
        ContextInterface $context,
        ColumnFactory $columnFactory,
        Repository $attributeRepository,
        InlineEditUpdater $inlineEditUpdater,
        Data $helper,
        BookmarkManagementInterface $bookmarkManagement,
        MagentoVersion $magentoVersion,
        Manager $moduleManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
        $this->inlineEditUpdater = $inlineEditUpdater;
        $this->bookmarkManagement = $bookmarkManagement;
        $this->helper = $helper;
        $this->magentoVersion = $magentoVersion;
        $this->moduleManager = $moduleManager;
    }

    protected function getFilterType($frontendInput)
    {
        return $this->filterMap[$frontendInput] ?? $this->filterMap['default'];
    }

    private function isMagentoInventorySalesAdminUiEnable()
    {
        return $this->moduleManager->isEnabled(self::MAGENTO_INVENTORY_SALES_ADMIN_UI_MODULE_NAMESPACE);
    }

    public function prepare()
    {
        $visibleColumns = $this->_getVisibleColumns();

        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;

        foreach ($this->attributeRepository->getList() as $attribute) {
            $config = [];
            if (!isset($this->components[$attribute->getAttributeCode()]) &&
                !in_array($attribute->getAttributeCode(), $this->skipAttributes) &&
                $attribute->getIsUsedInGrid()
            ) {
                $config['sortOrder'] = ++$columnSortOrder;
                $config['filter'] = $this->getFilterType($attribute->getFrontendInput());
                $config['isFilterableInGrid'] = $attribute->getIsFilterableInGrid();
                $config['amastyAttribute'] = true;
                $column = $this->columnFactory->create($attribute, $this->getContext(), $config);
                if (array_key_exists($attribute->getAttributeCode(), $visibleColumns)) {
                    $column->prepare();
                }

                $this->inlineEditUpdater->applyEditing(
                    $column,
                    $attribute->getFrontendInput(),
                    $attribute->getFrontendClass(),
                    $attribute->getIsRequired()
                );
                $this->addComponent($attribute->getAttributeCode(), $column);
            }
        }
        $this->_prepareConfig();
        $this->_prepareColumns();

        parent::prepare();
    }

    protected function _getVisibleColumns()
    {
        $visibleColumns = [];

        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            'product_listing'
        );

        if (is_object($bookmark)) {
            $config = $bookmark->getConfig();
            if (isset($config['current']['columns']) && is_array($config['current']['columns'])) {
                foreach ($config['current']['columns'] as $key => $column) {
                    if (isset($column['visible'])) {
                        if ($column['visible'] == true) {
                            $visibleColumns[$key] = $column;
                        }
                    }
                }
            }
        }

        return $visibleColumns;
    }

    protected function _prepareConfig()
    {
        $config = $this->getConfig();

        if (isset($config['amastyEditorConfig'])) {
            $config['amastyEditorConfig']['isMultiEditing'] =
                (string)$this->helper->getModuleConfig('editing/mode') == 'multi';
        }
        $this->setConfig($config);
    }

    protected function _prepareColumns()
    {
        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            'product_listing'
        );

        $config = $bookmark ? $bookmark->getConfig() : null;

        $bookmarksCols = is_array($config) &&
            is_array($config['current']) &&
            is_array($config['current']['columns']) ? $config['current']['columns'] : [];

        if (isset($this->components['salable_quantity'])
            && !$this->isMagentoInventorySalesAdminUiEnable()
        ) {
            unset($this->components['salable_quantity']);
        }

        foreach ($this->components as $id => $column) {
            if (!$column instanceof \Magento\Ui\Component\Listing\Columns\Column) {
                continue;
            }
            $config = $column->getData('config');
            $hasFilter = isset($config['filter']);

            if ($hasFilter) {
                $config['default_filter'] = $config['filter'];
            }
            $filterHidden = isset($bookmarksCols[$id]['ampgrid_filterable'])
                && $bookmarksCols[$id]['ampgrid_filterable'] === false;

            $isFilterableInGrid = isset($config['isFilterableInGrid']) ? $config['isFilterableInGrid'] : true;
            $hasFilter = $isFilterableInGrid && $hasFilter;
            $filter = $hasFilter ? $config['filter'] : null;

            if ($filterHidden || (!$isFilterableInGrid && !isset($bookmarksCols[$id]['ampgrid_filterable']))) {
                $filter = '';
            }
            $config['filter'] = $filter;
            $config['ampgrid'] = $this->prepareAmpgridConfig($bookmarksCols, $config, $id);
            $config['ampgrid']['has_filter'] = $hasFilter;

            if (in_array($column->getName(), $this->forceFilter)) {
                $config['ampgrid']['filterable'] = true;
                $config['ampgrid']['has_filter'] = true;
            }
            $config['ampgrid_def_label'] = isset($config['label']) ? $config['label'] : '';
            $config['label'] = $config['ampgrid']['title'];
            $config['ampgrid_editable'] = $config['ampgrid']['editable'];
            $config['ampgrid_marker'] = $config['ampgrid']['marker'];

            if ($column->getName() === 'sku') {
                $config = $this->prepareSkuColumn($config);
            }
            $column->setData('config', $config);
        }
    }

    private function prepareAmpgridConfig($bookmarksCols, $config, $id)
    {
        $result['has_editor'] = isset($config['editor']);
        $result['filterable'] = $bookmarksCols[$id]['ampgrid_filterable'] ?? !empty($config['filter']);
        $result['editable'] = $bookmarksCols[$id]['ampgrid_editable'] ?? false;
        $result['marker'] = $bookmarksCols[$id]['ampgrid_marker'] ?? false;

        $result['title'] = '';
        if (isset($bookmarksCols[$id]['ampgrid_title'])) {
            $result['title'] = $bookmarksCols[$id]['ampgrid_title'];
        } elseif (isset($config['label'])) {
            $result['title'] = $config['label'];
        }

        $result['visible'] = true;
        if (isset($bookmarksCols[$id]['visible'])) {
            $result['visible'] = $bookmarksCols[$id]['visible'];
        } elseif (isset($config['visible'])) {
            $result['visible'] = $config['visible'];
        }

        return $result;
    }

    /**
     * An existing product is initialized by sku when it is saved using the product repository
     * on Magento version 2.3.0 - 2.3.2.
     *
     * @param array $config
     *
     * @return array
     */
    private function prepareSkuColumn($config)
    {
        if (version_compare($this->magentoVersion->get(), '2.3.0', '>=')
            && version_compare($this->magentoVersion->get(), '2.3.2', '<=')
        ) {
            $config['ampgrid_editable'] = false;
            $config['ampgrid']['editable'] = false;
            $config['ampgrid']['has_editor'] = false;
        }

        return $config;
    }
}
