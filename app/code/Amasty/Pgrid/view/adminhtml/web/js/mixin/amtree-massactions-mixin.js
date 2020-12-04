define([
    'jquery',
    'Amasty_Pgrid/js/action/show-notification'
], function ($, showNotification) {
    'use strict';

    return function (target) {
        var allowedActionIndexes = [
            'amasty_removeoptions',
            'amasty_updateadvancedprices',
            'amasty_removeimg',
            'amasty_amdelete',
            'enable',
            'disable',
            'attributes',
            'assign-sources',
            'unassign-sources',
            'inventory-transfer'
        ];

        return target.extend({
            applyAction: function (actionIndex) {
                if (allowedActionIndexes.indexOf(actionIndex) !== -1) {
                    return showNotification.call(this, this._super);
                }

                return this._super.call(this);
            },

            applyMassaction: function () {
                window.isPactionEnabled = true;

                return showNotification.call(this, this._super);
            }
        });
    };
});
