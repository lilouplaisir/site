define([
    'jquery',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'Amasty_Pgrid/js/action/export-notification',
    'mage/translate'
], function ($, _, message, exportNotification) {
    'use strict';

    return function (target) {
        return target.extend({
            defaults: {
                selectionStorage: [],
                exportMode: '',
            },
            applyOption: function () {
                var isProductsSelected = this.selections().selected().length !== 0,
                    deferred = $.Deferred();

                if (!this.isProductListing()) {
                    return this._super();
                }

                exportNotification(deferred).showNotification(isProductsSelected);
                $.when(deferred).done(function (exportMode) {
                    switch (exportMode) {
                        case 'export-whole':
                            this.exportMode = exportMode;
                            this.selectionStorage = this.selections().selected().slice();
                            return this.processExport()
                        case 'export-selected':
                            this.exportMode = exportMode;
                            return this.processExport()
                    }
                }.bind(this));

                return false;
            },
            processExport: function() {
                if (!window.Blob) {
                    message({
                        title: $.mage.__('Attention'),
                        content: $.mage.__('Unable to export product because of using IE browser.'),
                    });
                    return false;
                }

                $('body').trigger('processStart');
                $.ajax({
                    url: this.getActiveOption().url,
                    type: 'POST',
                    data: this.getParams(),
                    success: function (response) {
                        var blob = new Blob([response], {type: 'text/plain'}),
                            link = $('<a>', {
                            href: URL.createObjectURL(blob),
                            download: 'export.' + this.getActiveOption().value
                        });
                        link[0].click();
                        $('body').trigger('processStop');
                    }.bind(this),
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        $('body').trigger('processStop');
                        console.log(errorThrown);
                    }
                });
            },
            getParams: function () {
                if (!this.isProductListing()) {
                    return this._super();
                }

                switch (this.exportMode) {
                    case 'export-whole':
                        this.selections().deselectAll().selectPage().excludeMode(false);
                        var result = this._super();
                        result.selected = result.selected.slice();
                        this.restoreSelection();
                        return result;
                    case 'export-selected':
                        return this._super();
                }
            },
            restoreSelection: function () {
                this.selections().deselectAll();
                _.each(this.selectionStorage, function (id) {
                    this.selections().select(id);
                }.bind(this));
            },
            isProductListing: function () {
                return this.ns === 'product_listing';
            }
        });
    };
});
