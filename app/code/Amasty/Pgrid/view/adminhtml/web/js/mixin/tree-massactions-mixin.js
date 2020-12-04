define([
    'jquery',
    'Amasty_Pgrid/js/action/show-notification'
], function ($, showNotification) {
    'use strict';

    return function (target) {
        return target.extend({
            applyAction: function (actionIndex) {
                if (actionIndex === 'status') {
                    return this._super.call(this);
                }

                return showNotification.call(this, this._super);
            }
        });
    };
});
