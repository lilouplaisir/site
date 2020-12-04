/**
* Copyright 2018 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    './filter/value',
    './filter/item/current',
    './url',
	'magnificPopup',
], function ($, filterValue, currentFilterItem, url) {
    'use strict';

    return {
        config: {
            mainColumn: 'div.column.main',
            navigation: '[data-role=filter-block]'
        },

        /**
         * Initialize
         *
         * @param {Object} config
         */
        init: function (config) {
            $.extend(this.config, config);
        },

        /**
         * Performs update blocks and window url, without scroll up to the top of page
         *
         * @param {String} windowUrl
         * @param {Object} blocksHtml
         */
        update: function (windowUrl, blocksHtml) {
            if (blocksHtml) {
                this._performUpdate(windowUrl, blocksHtml);
            }
			this.reInitFunction();
        },

        /**
         * Performs update blocks, window url and scroll up to the top of page
         *
         * @param {String} windowUrl
         * @param {Object} blocksHtml
         */
        updateAndScrollUpToTop: function (windowUrl, blocksHtml) {
            if (blocksHtml) {
                this._performUpdate(windowUrl, blocksHtml);
                $('body, html').animate({scrollTop: 0}, 0);
				this.reInitFunction();
            }
        },

        /**
         * Performs update blocks and window url
         *
         * @param {String} windowUrl
         * @param {Object} blocksHtml
         */
        _performUpdate: function (windowUrl, blocksHtml) {
            var mainColumnHtml = blocksHtml.mainColumn,
                navigationHtml = blocksHtml.navigation;

            $(this.config.mainColumn).replaceWith(mainColumnHtml);
            $(this.config.mainColumn).trigger('contentUpdated');

            $(this.config.navigation).replaceWith(navigationHtml);
            filterValue.reset();
            currentFilterItem.reset();
            $(this.config.navigation).trigger('contentUpdated');

            this._setCurrentUrl(windowUrl);
        },

        /**
         * Set current url
         *
         * @param {String} windowUrl
         */
        _setCurrentUrl: function (windowUrl) {
            url.setCurrentUrl(windowUrl);
            if (typeof(window.history.pushState) == 'function') {
                window.history.pushState(null, url.getCurrentUrl(), url.getCurrentUrl());
            } else {
                window.location.hash = '#!' + url.getCurrentUrl();
            }
        },
		
		reInitFunction: function(){
			var formKey = $("input[name*='form_key']").first().val();
			
			$(".mgs-quickview").bind("click", function() {
				var b = $(this).attr("data-quickview-url");
				b.length && reInitQuickview($, b)
			});
			
			$("img.lazy").unveil(25, function(){
				var self = $(this);
				setTimeout(function(){
					self.removeClass('lazy');
				}, 0);
			});
			
			$("input[name*='form_key']").val(formKey);
			
			var thisClass = this;
			
			$('button.tocart').click(function(event){
				event.preventDefault();
				var tag = $(this).parents('form:first');
				
				var data = tag.serializeArray();
				thisClass.initAjaxAddToCart(tag, 'catalog-add-to-cart-' + $.now(), tag.attr('action'), data);
				
				
			});
		},
		
		initAjaxAddToCart: function(tag, actionId, url, data){
				
			data.push({
				name: 'action_url',
				value: tag.attr('action')
			});
				
			var $addToCart = tag.find('.tocart').text();
				
			var self = this;
			data.push({
				name: 'ajax',
				value: 1
			});
			
			$.ajax({
				url: url,
				data: $.param(data),
				type: 'post',
				dataType: 'json',
				beforeSend: function(xhr, options) {
					if(ajaxCartConfig.animationType){
						$('#mgs-ajax-loading').show();
					}else{
						if(tag.find('.tocart').length){
							tag.find('.tocart').addClass('disabled');
							tag.find('.tocart').text('Adding...');
							tag.find('.tocart').attr('title','Adding...');
						}else{
							tag.addClass('disabled');
							tag.text('Adding...');
							tag.attr('title','Adding...');
						} 
						
					}
				},
				success: function(response, status) {
					if (status == 'success') {
						if(response.backUrl){
							data.push({
								name: 'action_url',
								value: response.backUrl
							});
							self.initAjaxAddToCart(tag, 'catalog-add-to-cart-' + $.now(), response.backUrl, data);
						}else{
							if (response.ui) {
								if(response.productView){
									$('#mgs-ajax-loading').hide();
										$.magnificPopup.open({
											items: {
												src: response.ui,
												type: 'iframe'
											},
											mainClass: 'success-ajax--popup',
											closeOnBgClick: false,
											preloader: true,
											tLoading: '',
											callbacks: {
												open: function() {
													$('#mgs-ajax-loading').hide();
													$('.mfp-preloader').css('display', 'block');
												},
												beforeClose: function() {
													var url_cart_update = ajaxCartConfig.updateCartUrl;
													$('[data-block="minicart"]').trigger('contentLoading');
													$.ajax({
														url: url_cart_update,
														method: "POST"
													});
												},
												close: function() {
													$('.mfp-preloader').css('display', 'none');
												},
												afterClose: function() {
													if(!response.animationType) {
														var $source = '';
														if(tag.find('.tocart').length){
															tag.find('.tocart').removeClass('disabled');
															tag.find('.tocart').text($addToCart);
															tag.find('.tocart').attr('title',$addToCart);
															if(tag.closest('.product-item-info').length){
																$source = tag.closest('.product-item-info');
																var width = $source.outerWidth();
																var height = $source.outerHeight();
															}else{
																$source = tag.find('.tocart');
																var width = 300;
																var height = 300;
															}
															
														}else{
															tag.removeClass('disabled');
															tag.text($addToCart);
															tag.attr('title',$addToCart);
															$source = tag.closest('.product-item-info');
															var width = $source.outerWidth();
															var height = $source.outerHeight();
														}
														
														$('html, body').animate({
															'scrollTop' : $(".minicart-wrapper").position().top
														},2000);
														var $animatedObject = $('<div class="flycart-animated-add" style="position: absolute;z-index: 99999;">'+response.image+'</div>');
														var left = $source.offset().left;
														var top = $source.offset().top;
														$animatedObject.css({top: top-1, left: left-1, width: width, height: height});
														$('html').append($animatedObject);
														var divider = 3;
														var gotoX = $(".minicart-wrapper").offset().left + ($(".minicart-wrapper").width() / 2) - ($animatedObject.width()/divider)/2;
														var gotoY = $(".minicart-wrapper").offset().top + ($(".minicart-wrapper").height() / 2) - ($animatedObject.height()/divider)/2;                                               
														$animatedObject.animate({
															opacity: 0.6,
															left: gotoX,
															top: gotoY,
															width: $animatedObject.width()/2,
															height: $animatedObject.height()/2
														}, 2000,
														function () {
															$(".minicart-wrapper").fadeOut('fast', function () {
																$(".minicart-wrapper").fadeIn('fast', function () {
																	$animatedObject.fadeOut('fast', function () {
																		$animatedObject.remove();
																	});
																});
															});
														});
													}
												}
											}
										});
								}else{
									var $content = '<div class="popup__main popup--result">'+response.ui + response.related + '</div>';
									if(response.animationType) {
										$('#mgs-ajax-loading').hide();
										$.magnificPopup.open({
											mainClass: 'success-ajax--popup',
											items: {
												src: $content,
												type: 'inline'
											},
											callbacks: {
												open: function() {
													$('#mgs-ajax-loading').hide();
												},
												beforeClose: function() {
													var url_cart_update = ajaxCartConfig.updateCartUrl;
													$('[data-block="minicart"]').trigger('contentLoading');
													$.ajax({
														url: url_cart_update,
														method: "POST"
													});
												}  
											}
										});
									}else{
										var $source = '';
										if(tag.find('.tocart').length){
											tag.find('.tocart').removeClass('disabled');
											tag.find('.tocart').text($addToCart);
											tag.find('.tocart').attr('title',$addToCart);
											if(tag.closest('.product-item-info').length){
												$source = tag.closest('.product-item-info');
												var width = $source.outerWidth();
												var height = $source.outerHeight();
											}else{
												$source = tag.find('.tocart');
												var width = 300;
												var height = 300;
											}
											
										}else{
											tag.removeClass('disabled');
											tag.text($addToCart);
											tag.attr('title',$addToCart);
											$source = tag.closest('.product-item-info');
											var width = $source.outerWidth();
											var height = $source.outerHeight();
										}
										
										$('html, body').animate({
											'scrollTop' : $(".minicart-wrapper").position().top
										},2000);
										var $animatedObject = $('<div class="flycart-animated-add" style="position: absolute;z-index: 99999;">'+response.image+'</div>');
										var left = $source.offset().left;
										var top = $source.offset().top;
										$animatedObject.css({top: top-1, left: left-1, width: width, height: height});
										$('html').append($animatedObject);
										var divider = 3;
										var gotoX = $(".minicart-wrapper").offset().left + ($(".minicart-wrapper").width() / 2) - ($animatedObject.width()/divider)/2;
										var gotoY = $(".minicart-wrapper").offset().top + ($(".minicart-wrapper").height() / 2) - ($animatedObject.height()/divider)/2;                                               
										$animatedObject.animate({
											opacity: 0.6,
											left: gotoX,
											top: gotoY,
											width: $animatedObject.width()/2,
											height: $animatedObject.height()/2
										}, 2000,
										function () {
											$(".minicart-wrapper").fadeOut('fast', function () {
												$(".minicart-wrapper").fadeIn('fast', function () {
													$animatedObject.fadeOut('fast', function () {
														$animatedObject.remove();
													});
												});
											});
										});
									}
								}
							}
						}                            
					}
				},
				error: function() {
					$('#mgs-ajax-loading').hide();
					window.location.href = ajaxCartConfig.redirectCartUrl;
				}
			});
		},
    };
});
