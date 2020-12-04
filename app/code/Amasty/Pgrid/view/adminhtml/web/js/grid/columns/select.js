/**
 * Pgrid Select Component
 */
define([
    'Magento_Ui/js/grid/columns/select',
    'Amasty_Pgrid/js/model/column'
], function (Select, amColumn) {
    'use strict';

    return Select.extend(amColumn);
});
