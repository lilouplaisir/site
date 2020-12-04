define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, message, $t) {
    'use strict';

    return function (deferred) {
        return {
            modalConfig: {
                modalClass: 'ampgrid-modal-container',
                closed: function () {
                    return deferred.resolve();
                },
                buttons: [{
                    text: $t('Export Selected Products'),
                    class: 'secondary',
                    click: function () {
                        this.closeModal();
                        return deferred.resolve('export-selected');
                    }
                }, {
                    text: $t('Export Whole Page'),
                    class: 'secondary',
                    click: function () {
                        this.closeModal();
                        return deferred.resolve('export-whole');
                    }
                }, {
                    text: $t('Dismiss'),
                    class: 'primary',
                    click: function () {
                        this.closeModal();
                    }
                }],
                content: $t('Please select product export option.') +
                    $t('You can select specific products on grid to export, instead of all products on page.')
            },
            showNotification: function (isProductsSelected) {
                if (!isProductsSelected) {
                    this.modalConfig.buttons[0].class += ' hidden';
                }
                message(this.modalConfig);
            }
        };
    };
});
