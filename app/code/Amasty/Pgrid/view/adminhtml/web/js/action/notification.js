define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Amasty_Pgrid/js/action/save',
    'uiRegistry',
    'mage/translate'
], function ($, message, saveAction, registry, $t) {
    'use strict';

    return function (deferred) {
        return {
            modalConfig: {
                modalClass: 'ampgrid-modal-container',
                editorName: 'product_listing.product_listing.product_columns_amasty_editor',
                closed: function () {
                    deferred.resolve();
                },
                buttons: [{
                    text: $t('Proceed'),
                    click: function () {
                        this.closeModal();
                    }
                }, {
                    text: $t('Save'),
                    click: function () {
                        var editor = registry.get(this.options.editorName),
                            deferredSave = $.Deferred();

                        saveAction(editor, deferredSave);
                        $.when(deferredSave).done(function () {
                            this.closeModal();
                        }.bind(this));
                    }
                }],
                content: $t('You have unsaved changes on this page.') +
                    $t('Do you want to save the changes or proceed without saving?')
            },
            showNotification: function () {
                message(this.modalConfig);
            }
        };
    };
});
