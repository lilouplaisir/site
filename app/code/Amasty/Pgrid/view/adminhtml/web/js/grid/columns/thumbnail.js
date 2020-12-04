/**
 * Pgrid Thumbnail Component
 */
define([
    'Magento_Ui/js/grid/columns/thumbnail',
    'Amasty_Pgrid/js/model/column'
], function (Thumbnail, amColumn) {
    'use strict';

    return Thumbnail.extend(amColumn);
});
