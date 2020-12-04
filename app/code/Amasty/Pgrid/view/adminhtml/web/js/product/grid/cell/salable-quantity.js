/**
 * Pgrid Salable Quantity Component
 */
define([
    'Magento_InventorySalesAdminUi/js/product/grid/cell/salable-quantity',
    'Amasty_Pgrid/js/model/column'
], function (SalableQuantity, amColumn) {
    'use strict';

    return SalableQuantity.extend(amColumn);
});
