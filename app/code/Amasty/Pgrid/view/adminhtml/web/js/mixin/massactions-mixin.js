define([
    'jquery',
    'Amasty_Pgrid/js/action/show-notification'
], function ($, showNotification) {
    'use strict';

    return function (target) {
        return target.extend({
            applyMassaction: function () {
                window.isPactionEnabled = false;

                return showNotification.call(this, this._super);
            },

            close: function () {
                if (!window.isPactionEnabled) {
                    return this._super.call(this);
                }
            }
        });
    };
});
