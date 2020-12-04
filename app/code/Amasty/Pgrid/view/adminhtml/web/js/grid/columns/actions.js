/**
 * Pgrid Actions Component
 */
define([
    'Magento_Ui/js/grid/columns/actions',
    'Amasty_Pgrid/js/model/column'
], function (Actions, amColumn) {
    'use strict';

    return Actions.extend(amColumn);
});
