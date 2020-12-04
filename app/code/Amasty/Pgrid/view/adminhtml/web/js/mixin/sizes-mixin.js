define([
    'jquery',
    'Amasty_Pgrid/js/action/show-notification'
], function ($, showNotification) {
    'use strict';

    return function (target) {
        return target.extend({
            setSize: function () {
                return showNotification.call(this, this._super);
            }
        });
    };
});
