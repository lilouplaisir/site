define([
    'jquery',
    'MGS_AjaxCart/js/config',
    'magnificPopup'
], function ($, mgsConfig) {
    "use strict";

    jQuery.widget('mgs.action', {
        options: {
            requestParamName: mgsConfig.requestParamName
        },
        fire: function (tag, actionId, url, data, redirectToCatalog) {
            this._fire(tag, actionId, url, data);
        },
        _fire: function (tag, actionId, url, data) {
            var textCart = $.mage.__('Add To Cart');
            var self = this;
            data.push({
                name: this.options.requestParamName,
                value: 1
            });

            jQuery.ajax({
                url: url,
                data: jQuery.param(data),
                type: 'post',
                dataType: 'json',
                beforeSend: function (xhr, options) {
                    if (mgsConfig.animationType) {
                        jQuery('#mgs-ajax-loading').show();
                    } else {
                        if (tag.find('.tocart').length) {
                            tag.find('.tocart').addClass('disabled');
                            tag.find('.tocart .icon').removeClass('pe-7s-shopbag');
                            tag.find('.tocart .icon').addClass('fa-spin pe-7s-config');
                            tag.find('.tocart .text').text('Adding...');
                            tag.find('.tocart').attr('title', 'Adding...');
                            /* Add code */
                            tag.attr('title', 'Adding...');
                            $("#product-addtocart-button > span").text('Adding...');
                        } else {
                            tag.addClass('disabled');
                            tag.attr('title', 'Adding...');
                        }

                    }
                },
                success: function (response, status) {
                    if (status == 'success') {
                        if (response.backUrl) {
                            data.push({
                                name: 'action_url',
                                value: response.backUrl
                            });
                            self._fire(tag, actionId, response.backUrl, data);
                        } else {
                            if (response.ui) {
                                if (response.productView) {
                                    jQuery('#mgs-ajax-loading').hide();
                                    // Add code
                                    if (!response.lisProduct && CATALOG_CHECK == 2) {
                                        $("#product-addtocart-button > span").text('Add to cart');
                                        return;
                                    }
                                    jQuery.magnificPopup.open({
                                        items: {
                                            src: response.ui,
                                            type: 'iframe'
                                        },
                                        mainClass: 'success-ajax--popup',
                                        closeOnBgClick: false,
                                        preloader: true,
                                        tLoading: '',
                                        callbacks: {
                                            open: function () {
                                                jQuery('#mgs-ajax-loading').hide();
                                                jQuery('.mfp-preloader').css('display', 'block');
                                            },
                                            beforeClose: function () {
                                                var url_cart_update = mgsConfig.updateCartUrl;
                                                jQuery('[data-block="minicart"]').trigger('contentLoading');
                                                jQuery.ajax({
                                                    url: url_cart_update,
                                                    method: "POST"
                                                });
                                            },
                                            close: function () {
                                                jQuery('.mfp-preloader').css('display', 'none');
                                            },
                                            afterClose: function () {
                                                if (!response.animationType) {
                                                    if (!parent.jQuery.magnificPopup.instance.isOpen) {
                                                        if (jQuery('html').hasClass('add-item-success')) {
                                                            var $source = '';
                                                            if (tag.find('.tocart').length) {
                                                                tag.find('.tocart').removeClass('disabled');
                                                                tag.find('.tocart .text').text(textCart);
                                                                tag.find('.tocart .icon').removeClass('pe-7s-config');
                                                                tag.find('.tocart .icon').removeClass('fa-spin');
                                                                tag.find('.tocart .icon').addClass('pe-7s-shopbag');
                                                                if (tag.closest('.product-item-info').length) {
                                                                    $source = tag.closest('.product-item-info');
                                                                    var width = $source.outerWidth();
                                                                    var height = $source.outerHeight();
                                                                } else {
                                                                    $source = tag.find('.tocart');
                                                                    var width = 300;
                                                                    var height = 300;
                                                                }

                                                            } else {
                                                                tag.removeClass('disabled');
                                                                tag.find('.icon').removeClass('fa-spin');
                                                                tag.find('.text').text(textCart);
                                                                tag.find('.icon').removeClass('pe-7s-config');
                                                                tag.find('.icon').addClass('pe-7s-shopbag');
                                                                $source = tag.closest('.product-item-info');
                                                                var width = $source.outerWidth();
                                                                var height = $source.outerHeight();
                                                            }

                                                            var $animatedObject = jQuery('<div class="flycart-animated-add" style="position: absolute;z-index: 99999;">' + response.image + '</div>');

                                                            var left = $source.offset().left;
                                                            var top = $source.offset().top;

                                                            $animatedObject.css({ top: top - 1, left: left - 1 });
                                                            jQuery('html').append($animatedObject);

                                                            jQuery('#footer-cart-trigger').addClass('active');
                                                            jQuery('#footer-mini-cart').slideDown(300);

                                                            var gotoX = jQuery("#fixed-cart-footer").offset().left + 20;
                                                            var gotoY = jQuery("#fixed-cart-footer").offset().top;
                                                            $animatedObject.animate({
                                                                opacity: 0.6,
                                                                left: gotoX,
                                                                top: gotoY
                                                            }, 2000,
                                                                function () {
                                                                    $animatedObject.fadeOut('fast', function () {
                                                                        $animatedObject.remove();
                                                                        jQuery('html').removeClass('add-item-success');
                                                                    });
                                                                });
                                                        } else {
                                                            tag.removeClass('disabled');
                                                            tag.find('.icon').removeClass('fa-spin');
                                                            tag.find('.text').text(textCart);
                                                            tag.find('.icon').removeClass('pe-7s-config');
                                                            tag.find('.icon').addClass('pe-7s-shopbag');
                                                            $source = tag.closest('.product-item-info');
                                                            var width = $source.outerWidth();
                                                            var height = $source.outerHeight();
                                                        }
                                                    } else {
                                                        var $content = '<div></div><div class="popup__main popup--result">' + response.ui + response.related + '</div>';
                                                        jQuery('#mgs-ajax-loading').hide();
                                                        parent.jQuery.magnificPopup.instance.items[0] = { src: $content, type: 'inline' };
                                                        parent.jQuery('.mfp-mgs-quickview').addClass('success-ajax--popup');
                                                        parent.jQuery.magnificPopup.instance.updateItemHTML();
                                                        parent.truncateOptions();
                                                        parent.replaceStrings();
                                                    }
                                                }
                                            }
                                        }
                                    });
                                } else {
                                    /**Add code */
                                    if (response.animationType == 'popup' || response.animationType == 'cartshow') {
                                        if (response.animationType == 'popup') {
                                            /* Popup Type */
                                            var $content = '<div></div><div class="popup__main popup--result">' + response.ui + response.related + '</div>';
                                            jQuery('#mgs-ajax-loading').hide();
                                            if (parent.jQuery.magnificPopup.instance.isOpen) {
                                                parent.jQuery.magnificPopup.instance.items[0] = { src: $content, type: 'inline' };
                                                parent.jQuery('.mfp-mgs-quickview').addClass('success-ajax--popup');
                                                parent.jQuery.magnificPopup.instance.updateItemHTML();
                                                parent.truncateOptions();
                                                parent.replaceStrings();
                                            } else {
                                                jQuery.magnificPopup.open({
                                                    mainClass: 'success-ajax--popup',
                                                    items: {
                                                        src: $content,
                                                        type: 'inline'
                                                    },
                                                    callbacks: {
                                                        open: function () {
                                                            jQuery('#mgs-ajax-loading').hide();
                                                        },
                                                        beforeClose: function () {
                                                            var url_cart_update = mgsConfig.updateCartUrl;
                                                            jQuery('[data-block="minicart"]').trigger('contentLoading');
                                                            jQuery.ajax({
                                                                url: url_cart_update,
                                                                method: "POST"
                                                            });
                                                        }
                                                    }
                                                });
                                            }
                                        }

                                        if (response.animationType == 'cartshow') {
                                            if (parent.jQuery.magnificPopup.instance.isOpen) { 
                                                parent.jQuery.magnificPopup
                                                  .instance.close();
                                            }
                                            $('.minicart-wrapper .action.showcart').click();
                                            tag.find('.tocart .text').text('Add to cart');
                                            tag.find('.tocart').attr('title', 'Add to cart');
                                            tag.find('.icon').removeClass('fa-spin');
                                            // Add code
                                            $("#product-addtocart-button > span").text('Add to cart');
                                        }
                                    } else {
                                        if (!parent.jQuery.magnificPopup.instance.isOpen) {
                                            /* Fly Cart Type */
                                            var $source = '';
                                            if (tag.find('.tocart').length) {
                                                tag.find('.tocart').removeClass('disabled');
                                                tag.find('.tocart .text').text(textCart);
                                                tag.find('.tocart .icon').removeClass('pe-7s-config');
                                                tag.find('.tocart .icon').removeClass('fa-spin');
                                                tag.find('.tocart .icon').addClass('pe-7s-shopbag');
                                                if (tag.closest('.product-item-info').length) {
                                                    $source = tag.closest('.product-item-info');
                                                    var width = $source.outerWidth();
                                                    var height = $source.outerHeight();
                                                } else {
                                                    $source = tag.find('.tocart');
                                                    var width = 300;
                                                    var height = 300;
                                                }

                                            } else {
                                                tag.removeClass('disabled');
                                                tag.find('.icon').removeClass('fa-spin');
                                                tag.find('.text').text(textCart);
                                                tag.find('.icon').removeClass('pe-7s-config');
                                                tag.find('.icon').addClass('pe-7s-shopbag');
                                                $source = tag.closest('.product-item-info');
                                                var width = $source.outerWidth();
                                                var height = $source.outerHeight();
                                            }

                                            var $animatedObject = jQuery('<div class="flycart-animated-add" style="position: absolute;z-index: 99999;">' + response.image + '</div>');
                                            var left = $source.offset().left;
                                            var top = $source.offset().top;

                                            $animatedObject.css({ top: top - 1, left: left - 1, width: width, height: height });
                                            jQuery('html').append($animatedObject);

                                            var gotoX = jQuery("#fixed-cart-footer").offset().left + 20;
                                            var gotoY = jQuery("#fixed-cart-footer").offset().top;

                                            jQuery('#footer-cart-trigger').addClass('active');
                                            jQuery('#footer-mini-cart').slideDown(300);

                                            $animatedObject.animate({
                                                opacity: 0.6,
                                                left: gotoX,
                                                top: gotoY,
                                                width: $animatedObject.width() / 2,
                                                height: $animatedObject.height() / 2
                                            }, 2000,
                                                function () {
                                                    jQuery(".minicart-wrapper").fadeOut('fast', function () {
                                                        jQuery(".minicart-wrapper").fadeIn('fast', function () {
                                                            $animatedObject.fadeOut('fast', function () {
                                                                $animatedObject.remove();
                                                            });
                                                        });
                                                    });
                                                });
                                        } else {
                                            parent.jQuery.magnificPopup.close();

                                            var $animatedObject = parent.jQuery('<div class="flycart-animated-add" style="position: fixed;z-index: 99999; bottom: 50%; left: 50%;">' + response.image + '</div>');

                                            parent.jQuery('html').append($animatedObject);

                                            var gotoX = parent.jQuery("#fixed-cart-footer").offset().left + 20;
                                            var gotoY = parent.jQuery("#fixed-cart-footer").offset().top;

                                            parent.jQuery('#footer-cart-trigger').addClass('active');
                                            parent.jQuery('#footer-mini-cart').slideDown(300);


                                            $animatedObject.animate({
                                                opacity: 0.6,
                                                left: 20,
                                                bottom: 0
                                            }, 2000,
                                                function () {
                                                    $animatedObject.fadeOut('fast', function () {
                                                        $animatedObject.remove();
                                                    });
                                                });
                                        }
                                    }
                                }
                            }
                        }
                    }
                },
                error: function () {
                    jQuery('#mgs-ajax-loading').hide();
                    window.location.href = mgsConfig.redirectCartUrl;
                }
            });
        }
    });

    return jQuery.mgs.action;
});