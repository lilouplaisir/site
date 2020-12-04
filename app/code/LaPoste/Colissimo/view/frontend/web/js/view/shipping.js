define([
    'jquery',
    'lpc',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/checkout-data',
], function (
    $,
    lpc,
    modal,
    quote,
    addressList,
    checkoutData,
) {
    'use strict';

    var mixin = {
        selectShippingMethod: function (shippingMethod) {
            lpc.lpcPublicSetRelayId('');
            lpc.lpcAttachOnClickPopup(shippingMethod, modal, quote, addressList, checkoutData);
            return this._super();
        },

        setShippingInformation: function () {
            if (this.validateShippingInformation() && this.lpcValidateChoiceRelay()) {
                this._super();
            }
        },

        lpcValidateChoiceRelay: function () {
            if (!lpc.lpcGetRelayId() &&
                this.isShippingMethodRelayPoint()) {
                this.errorValidationMessage($.mage.__('Please choose a relay for this shipping method'));
                return false;
            } else {
                lpc.lpcPublicSetRelayId('');
                return true;
            }
        },

        isShippingMethodRelayPoint: function () {
            return quote.shippingMethod().carrier_code === 'colissimo' &&
                quote.shippingMethod().method_code.indexOf('pr') !== -1;
        },


        /**
         * @return {Boolean}
         */
        validateShippingInformation: function () {
            var result = this._super();

            if (this.isShippingMethodRelayPoint()) {
                var shippingAddress = quote.shippingAddress();
                shippingAddress['save_in_address_book'] = 0;
            }

            return result;
        }

    };

    return function (target) {
        return target.extend(mixin);
    }
});