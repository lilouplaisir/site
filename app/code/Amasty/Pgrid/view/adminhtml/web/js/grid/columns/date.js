/**
 * Pgrid Date Component
 */
define([
    'Magento_Ui/js/grid/columns/date',
    'Amasty_Pgrid/js/model/column',
    'underscore'
], function (Date, amColumn, _) {
    'use strict';

    var column = _.extend({
        defaults: {
            headerTmpl: 'Amasty_Pgrid/ui/grid/columns/text'
        }
    }, amColumn);

    return Date.extend(column);
});
