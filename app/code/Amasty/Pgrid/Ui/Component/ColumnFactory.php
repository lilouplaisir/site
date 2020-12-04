<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


namespace Amasty\Pgrid\Ui\Component;

class ColumnFactory extends \Magento\Catalog\Ui\Component\ColumnFactory
{
    protected $jsComponentMap = [
        'text' => 'Amasty_Pgrid/js/grid/columns/column',
        'select' => 'Amasty_Pgrid/js/grid/columns/select',
        'date' => 'Amasty_Pgrid/js/grid/columns/date',
        'multiselect' => 'Amasty_Pgrid/js/grid/columns/multiselect',
    ];

    /**
     * @var array
     */
    protected $dataTypeMap = [
        'default' => 'text',
        'text' => 'text',
        'boolean' => 'select',
        'select' => 'select',
        'multiselect' => 'multiselect',
        'date' => 'date',
    ];

    public function create($attribute, $context, array $config = [])
    {
        $columnName = $attribute->getAttributeCode();
        $config = array_merge([
            'label' => __($attribute->getDefaultFrontendLabel()),
            'dataType' => $this->getDataType($attribute),
            'add_field' => true,
            'visible' => $attribute->getIsVisibleInGrid(),
            'filter' => ($attribute->getIsFilterableInGrid())
                ? $this->getFilterType($attribute->getFrontendInput())
                : null,
        ], $config);

        /*
         * check name of column for exclude Role Permission Owner and
         * check Weight Type for show valid label
         */
        if ($attribute->usesSource() && $columnName !== 'amrolepermissions_owner') {
            $config['options'] = $attribute->getSource()->getAllOptions();
        } elseif ($attribute->getAttributeCode() === 'weight_type') {
            $config['options'] = [
                [
                    'label' => __('This item has weight'),
                    'value' => 1
                ],
                [
                    'label' => __('This item has no weight'),
                    'value' => 0
                ],
            ];
        }

        $config['component'] = $this->getJsComponent($config['dataType']);

        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];

        return $this->componentFactory->create($columnName, 'column', $arguments);
    }
}
