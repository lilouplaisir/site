define([
    'jquery',
    'Amasty_Pgrid/js/action/notification'
], function ($, notification) {
    'use strict';

    return function (_super) {
        var deferred;

        if (!window.isPgridEditable || window.isNotificationEnabled) {
            return _super.call(this);
        }

        deferred = $.Deferred();
        notification(deferred).showNotification();
        window.isNotificationEnabled = true;

        $.when(deferred).done(function () {
            window.isNotificationEnabled = false;
            window.isPgridEditable = false;

            return _super.call(this);
        }.bind(this));
    };
});
