/**
 * Pgrid Multiselect Component
 */
define([
    'underscore',
    'Magento_Ui/js/grid/columns/column',
    'Amasty_Pgrid/js/model/column'
], function (_, Column, amColumn) {
    'use strict';

    var column = _.extend({
        /**
         * Retrieves label associated with a provided value.
         *
         * @returns {String}
         */
        getLabel: function () {
            var options = this.options || [],
                values = this._super(),
                label = [];

            if (!Array.isArray(values) && values) {
                values = values.split(',');
            } else {
                values = [];
            }

            values = values.map(function (value) {
                return value + '';
            });

            options.forEach(function (item) {
                if (_.contains(values, item.value + '')) {
                    label.push(item.label);
                }
            });

            return label.join(', ');
        }
    }, amColumn);

    return Column.extend(column);
});
