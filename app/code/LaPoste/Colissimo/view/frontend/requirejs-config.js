var config = {
    map:{
        '*': {
            lpc: 'LaPoste_Colissimo/js/lpc'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {'LaPoste_Colissimo/js/view/shipping': true},
            'Magento_Checkout/js/action/set-shipping-information': {'LaPoste_Colissimo/js/action/set-shipping-information': true}
        }
    }
};