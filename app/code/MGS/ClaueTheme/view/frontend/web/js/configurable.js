/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'priceUtils',
    'priceBox',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'zoom-images',
    'mgs/slick',
    'mgs/owlcarousel',
    'mage/translate',
    'magnificPopup'
], function ($, _, mageTemplate) {
    'use strict';

    return function (widget) {
        $.widget('mage.configurable', widget, {


            /**
             * Initialize tax configuration, initial settings, and options values.
             * @private
             */
            _initializeOptions: function () {
                var options = this.options,
                    gallery = $(options.mediaGallerySelector),
                    galleryTemplate = $('#mgs_template_layout').val(),
                    priceBoxOptions = $(this.options.priceHolderSelector).priceBox('option').priceConfig || null;

                if (priceBoxOptions && priceBoxOptions.optionTemplate) {
                    options.optionTemplate = priceBoxOptions.optionTemplate;
                }

                if (priceBoxOptions && priceBoxOptions.priceFormat) {
                    options.priceFormat = priceBoxOptions.priceFormat;
                }
                options.optionTemplate = mageTemplate(options.optionTemplate);

                options.settings = options.spConfig.containerId ?
                    $(options.spConfig.containerId).find(options.superSelector) :
                    $(options.superSelector);

                options.values = options.spConfig.defaultValues || {};
                options.parentImage = $('[data-role=base-image-container] img').attr('src');

                this.inputSimpleProduct = this.element.find(options.selectSimpleProduct);

                var currentImages = [];

                $(".product.media .item-image").each(function (index) {
                    var item = [];
                    var url_video = "";
                    var type = 'image';

                    if ($(this).find('.popup-youtube').length) {
                        url_video = $(this).find('.popup-youtube').attr('href');
                    } else if ($(this).find('.lb.video-link').length) {
                        url_video = $(this).find('.lb.video-link').attr('href');
                    }
                    if (url_video) {
                        type = 'video';
                    }

                    item['zoom'] = $(this).attr('data-zoom');
                    item['full'] = $(this).find('.img-responsive').attr('src');
                    item['thumb'] = $(this).find('.img-responsive').attr('src');
                    item['type'] = type;
                    item['videoUrl'] = url_video;
                    //Add code
                    item['caption'] = $(this).find('.img-responsive').attr('alt');
                    currentImages.push(item);
                });

                options.mediaGalleryInitial = currentImages;

            },
            /**
             * Change displayed product image according to chosen options of configurable product
             * @private
             */
            _changeProductImage: function () {
                var images,
                    imagesToUpdate,
                    initialImages = this.options.mediaGalleryInitial,
                    zoomimg = $('#zoom_image').val(),
                    glr_layout = $('#glr_layout').val(),
                    lbox_image = $('#lbox_image').val();


                if (this.options.spConfig.images[this.simpleProduct]) {
                    images = $.extend(true, [], this.options.spConfig.images[this.simpleProduct]);
                }

                if (images) {
                    imagesToUpdate = images;
                } else {
                    imagesToUpdate = initialImages;
                }

                /* Update Gallery */
                if (imagesToUpdate) {
                    if (this.options.onlyMainImg) {
                        this.updateOneImage(imagesToUpdate);
                    } else {
                        if (imagesToUpdate.length == 1) {
                            this.updateOneImage(imagesToUpdate);
                        } else {

                            if (glr_layout == 2) {
                                this.updateBaseImageList(imagesToUpdate);
                            } else if (glr_layout == 4) {
                                this.updateBaseImageVertical(imagesToUpdate);
                            } else if (glr_layout == 0) {
                                this.updateBaseImageHorizontal(imagesToUpdate);
                            } else if (glr_layout == 1) {
                                this.updateGalleryGrid(imagesToUpdate);
                            } else if (glr_layout == 5) {
                                this.updateBaseImageOwl(imagesToUpdate);
                            }
                        }
                    }
                }

                if (zoomimg == 1) {
                    this.zoomImage();
                }

                if (lbox_image == 1) {
                    this.lightBoxGallery();
                }

                this.videoPopup();
            },

            videoPopup: function () {
                $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
                    type: 'iframe',
                    preloader: false,
                    mainClass: 'mfp-img-gallery',
                    fixedContentPos: true
                });
            },

            updateBaseImageOwl: function (imagesToUpdate) {
                var img_change = "";
                var view_type = $('#view_type').val();

                img_change = '<div id="owl-carousel-gallery" class="owl-carousel gallery-5">' + this.generateHtmlImage(imagesToUpdate) + '</div>';

                $(".product.media").html(img_change);

                if (view_type == 'quickview') {
                    $('#owl-carousel-gallery').on(' initialized.owl.carousel', function (event) {
                        setTimeout(function () {
                            var hs = $('#owl-carousel-gallery').height();
                            $('.product-info-main').height(hs);
                        }, 100);
                    });
                }

                $('#owl-carousel-gallery').owlCarousel({
                    items: 1,
                    autoplay: false,
                    lazyLoad: false,
                    nav: true,
                    dots: false,
                    navText: [$.mage.__('Prev'), $.mage.__('Next')],
                    rtl: RTL,
                });

                if (view_type == 'quickview') {
                    $('#owl-carousel-gallery').on('resized.owl.carousel', function (event) {
                        setTimeout(function () {
                            var hs = $('#owl-carousel-gallery').height();
                            $('.product-info-main').height(hs);
                        }, 100);
                    });
                }
            },

            updateBaseImageList: function (imagesToUpdate) {
                var img_change = "";

                img_change = '<div class="gallery-list">' + this.generateHtmlImage(imagesToUpdate) + '</div>';

                $(".product.media").html(img_change);
            },

            updateGalleryGrid: function (imagesToUpdate) {
                var img_change = "";
                var view_type = $('#view_type').val();

                img_change += '<div class="row">';
                var count = 0;
                $.each(imagesToUpdate, function (index) {
                    count++;
                    img_change += '<div class="item col-xs-6">';
                    if (lbox_image == 1) {
                        var href = imagesToUpdate[index].zoom;
                        var cla = 'lb';
                        if (imagesToUpdate[index].type == 'video' && imagesToUpdate[index].videoUrl != "") {
                            href = imagesToUpdate[index].videoUrl;
                            cla = 'lb video-link';
                        }
                        img_change = img_change + '<div class="product item-image imgzoom" data-zoom="' + imagesToUpdate[index].zoom + '"><a href="' + href + '" class="' + cla + '"><img class="img-responsive" src="' + imagesToUpdate[index].full + '" alt="abc"/></a></div>';
                    } else {
                        img_change = img_change + '<div class="product item-image imgzoom" data-zoom="' + imagesToUpdate[index].zoom + '"><img class="img-responsive" src="' + imagesToUpdate[index].full + '" alt="abc"/>';
                        if (imagesToUpdate[index].type == 'video' && imagesToUpdate[index].videoUrl != "") {
                            img_change = img_change + '<a target="_blank" class="popup-youtube btn btn-primary" href="' + imagesToUpdate[index].videoUrl + '">' + $.mage.__('Watch Video') + '</a>';
                        }
                        img_change = img_change + '</div>';
                    }
                    if (count % 2 == 0) {
                        img_change += '<div class="clearfix"></div>';
                    }
                    img_change += '</div>';
                });
                img_change += '</div>';

                $(".product.media").html(img_change);
            },

            updateBaseImageHorizontal: function (imagesToUpdate) {
                var img_change = "";

                img_change = '<div id="owl-carousel-gallery" class="owl-carousel gallery-horizontal">' + this.generateHtmlImage(imagesToUpdate) + '</div>';

                img_change = img_change + '<div id="horizontal-thumbnail" class="owl-carousel horizontal-thumbnail">' + this.generateHtmlThumb(imagesToUpdate) + '</div>';

                $(".product.media").html(img_change);

                $('#owl-carousel-gallery').owlCarousel({
                    items: 1,
                    autoplay: false,
                    lazyLoad: false,
                    nav: true,
                    dots: false,
                    navText: ["<span class='fa fa-angle-left'></span>", "<span class='fa fa-angle-right'></span>"],
                    rtl: RTL
                });

                $('#owl-carousel-gallery').on('changed.owl.carousel', function (event) {
                    var index = event.item.index;
                    $('#horizontal-thumbnail .item-thumb').removeClass('active');
                    $('#horizontal-thumbnail .item-thumb[data-owl=' + index + ']').addClass('active');
                    $('#horizontal-thumbnail').trigger('to.owl.carousel', index);
                });

                $('#horizontal-thumbnail').owlCarousel({
                    items: 4,
                    autoplay: false,
                    lazyLoad: false,
                    nav: true,
                    dots: false,
                    rtl: RTL,
                    navText: ["<span class='fa fa-angle-left'></span>", "<span class='fa fa-angle-right'></span>"],
                    responsive: {
                        0: {
                            items: 2
                        },
                        480: {
                            items: 2
                        },
                        768: {
                            items: 3
                        },
                        992: {
                            items: 4
                        }
                    }
                });

                $('#horizontal-thumbnail .item-thumb').click(function () {
                    $('#horizontal-thumbnail .item-thumb').removeClass('active');
                    var position = $(this).attr('data-owl');
                    $('#owl-carousel-gallery').trigger('to.owl.carousel', position);
                    $(this).addClass('active');
                });

            },

            updateBaseImageVertical: function (imagesToUpdate) {
                var img_change = "";

                img_change = '<div class="vertical-gallery">';

                img_change = img_change + '<div id="vertical-thumbnail-wrapper"><div id="vertical-thumbnails" class="vertical-thumbnail">' + this.generateHtmlThumb(imagesToUpdate) + '</div></div>';

                img_change = img_change + '<div id="owl-carousel-gallery" class="owl-carousel gallery-vertical">' + this.generateHtmlImage(imagesToUpdate) + '</div>';

                img_change = img_change + '</div>';

                $(".product.media").html(img_change);

                $('#owl-carousel-gallery').on('initialized.owl.carousel', function (event) {
                    setTimeout(function () {
                        var hs = $('#owl-carousel-gallery').height();
                        $('.product.media').height(hs);
                    }, 200);

                });

                $('#owl-carousel-gallery').owlCarousel({
                    items: 1,
                    autoplay: false,
                    lazyLoad: false,
                    nav: true,
                    dots: false,
                    navText: ["<span class='fa fa-angle-left'></span>", "<span class='fa fa-angle-right'></span>"],
                    rtl: RTL
                });

                $('#vertical-thumbnails img').load(function () {
                    setTimeout(function () {
                        $('#vertical-thumbnails').slick({
                            dots: false,
                            arrows: true,
                            vertical: true,
                            slidesToShow: 3,
                            slidesToScroll: 3,
                            verticalSwiping: true,
                            centerMode: true,
                            prevArrow: '<span class="pe-7s-angle-up"></span>',
                            nextArrow: '<span class="pe-7s-angle-down"></span>',
                            responsive: [
                                {
                                    breakpoint: 1199,
                                    settings: {
                                        slidesToShow: 2,
                                        slidesToScroll: 2
                                    }
                                },
                                {
                                    breakpoint: 768,
                                    settings: {
                                        slidesToShow: 3,
                                        slidesToScroll: 3
                                    }
                                },
                                {
                                    breakpoint: 600,
                                    settings: {
                                        slidesToShow: 2,
                                        slidesToScroll: 2
                                    }
                                },
                                {
                                    breakpoint: 360,
                                    settings: {
                                        slidesToShow: 1,
                                        slidesToScroll: 1
                                    }
                                }
                            ]
                        });
                    }, 200);
                });

                $('#owl-carousel-gallery').on('changed.owl.carousel', function (event) {
                    var index = event.item.index;
                    $('#vertical-thumbnails .item-thumb').removeClass('active');
                    $('#vertical-thumbnails .item-thumb[data-owl=' + index + ']').addClass('active');
                    var wdw = $(window).width();
                    var ci = imagesToUpdate.length;
                    if (wdw >= 1199 && ci > 3) {
                        $('#vertical-thumbnails').slick('slickGoTo', index);
                    } else if (wdw < 1199 && wdw >= 768 && ci > 2) {
                        $('#vertical-thumbnails').slick('slickGoTo', index);
                    } else if (wdw < 768 && wdw >= 600 && ci > 3) {
                        $('#vertical-thumbnails').slick('slickGoTo', index);
                    } else if (wdw < 768 && wdw >= 600 && ci > 2) {
                        $('#vertical-thumbnails').slick('slickGoTo', index);
                    } else if (wdw < 360) {
                        $('#vertical-thumbnails').slick('slickGoTo', index);
                    }
                });

                $('#owl-carousel-gallery').on('resized.owl.carousel', function (event) {
                    var hs = $('#owl-carousel-gallery').height();
                    $('.product.media').height(hs);
                });

                $('#vertical-thumbnails .item-thumb').click(function () {
                    $('#vertical-thumbnails .item-thumb').removeClass('active');
                    var position = $(this).attr('data-owl');
                    $('#owl-carousel-gallery').trigger('to.owl.carousel', position);
                    $(this).addClass('active');
                });
            },

            //Add code

            updateOneImage: function (imagesToUpdate) {
                var img_change = "",
                    lbox_image = $('#lbox_image').val();
                if (lbox_image == 1) {
                    var href = imagesToUpdate[0].zoom;
                    var cla = 'lb';
                    if (imagesToUpdate[0].type == 'video' && imagesToUpdate[0].videoUrl != "") {
                        href = imagesToUpdate[0].videoUrl;
                        cla = 'lb video-link';
                    }
                    img_change = img_change + '<div class="product single-image item-image base-image imgzoom" data-zoom="' + imagesToUpdate[0].zoom + '"><a href="' + href + '" class="' + cla + '"><img class="img-responsive" src="' + imagesToUpdate[0].full + '" alt="' + imagesToUpdate[0].caption + '"/></a></div>';
                } else {
                    img_change = img_change + '<div class="product single-image item-image base-image imgzoom" data-zoom="' + imagesToUpdate[0].zoom + '"><img class="img-responsive" src="' + imagesToUpdate[0].full + '" alt="' + imagesToUpdate[0].caption + '"/>';
                    if (imagesToUpdate[0].type == 'video' && imagesToUpdate[0].videoUrl != "") {
                        img_change = img_change + '<a target="_blank" class="popup-youtube btn btn-primary" href="' + imagesToUpdate[0].videoUrl + '">' + $.mage.__('Watch Video') + '</a>';
                    }
                    img_change = img_change + '</div>';
                }

                $(".product.media").height('auto');
                $(".product.media").html(img_change);
            },

            zoomImage: function () {
                $(".imgzoom").each(function (index) {
                    zoomElement(this);
                });
            },

            lightBoxGallery: function () {
                $('.product.media').magnificPopup({
                    delegate: '.imgzoom .lb',
                    type: 'image',
                    tLoading: 'Loading image #%curr%...',
                    mainClass: 'mfp-img-gallery',
                    fixedContentPos: true,
                    gallery: {
                        enabled: true,
                        navigateByImgClick: true,
                        preload: [0, 1]
                    },
                    iframe: {
                        markup: '<div class="mfp-figure">' +
                            '<div class="mfp-close"></div>' +
                            '<div class="mfp-img"></div>' +
                            '<div class="mfp-bottom-bar">' +
                            '<div class="mfp-title"></div>' +
                            '<div class="mfp-counter"></div>' +
                            '</div>' +
                            '</div>',
                    },
                    image: {
                        tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
                    },
                    callbacks: {
                        elementParse: function (item) {
                            if (item.el.context.className == 'lb video-link') {
                                item.type = 'iframe';
                            } else {
                                item.type = 'image';
                            }
                        }
                    }
                });
            },

            generateHtmlImage: function (imagesToUpdate) {
                var html = "",
                    lbox_image = $('#lbox_image').val();
                $.each(imagesToUpdate, function (index) {
                    if (lbox_image == 1) {
                        var href = imagesToUpdate[index].zoom;
                        var cla = 'lb';
                        if (imagesToUpdate[index].type == 'video' && imagesToUpdate[index].videoUrl != "") {
                            href = imagesToUpdate[index].videoUrl;
                            cla = 'lb video-link';
                        }
                        html = html + '<div class="product item-image imgzoom" data-zoom="' + imagesToUpdate[index].zoom + '"><a href="' + href + '" class="' + cla + '"><img class="img-responsive" src="' + imagesToUpdate[index].full + '" alt=""/></a></div>';
                    } else {
                        html = html + '<div class="product item-image imgzoom" data-zoom="' + imagesToUpdate[index].zoom + '"><img class="img-responsive" src="' + imagesToUpdate[index].full + '" alt=""/>';
                        if (imagesToUpdate[index].type == 'video' && imagesToUpdate[index].videoUrl != "") {
                            html = html + '<a target="_blank" class="popup-youtube btn btn-primary" href="' + imagesToUpdate[index].videoUrl + '">' + $.mage.__('Watch Video') + '</a>';
                        }
                        html = html + '</div>';
                    }
                });
                return html;
            },

            generateHtmlThumb: function (imagesToUpdate) {
                var html = "",
                    lbox_image = $('#lbox_image').val();

                $.each(imagesToUpdate, function (index) {
                    var classth = 'item-thumb';
                    if (index == 0) { classth = 'item-thumb active'; }

                    html = html + '<div class="' + classth + '" data-owl="' + index + '"><img class="img-responsive" src="' + imagesToUpdate[index].thumb + '" alt="abc"/>';
                    if (lbox_image != 1) {
                        if (imagesToUpdate[index].type == 'video' && imagesToUpdate[index].videoUrl != "") {
                            html = html + '<a target="_blank" class="popup-youtube" href="' + imagesToUpdate[index].videoUrl + '"></a>';
                        }
                    }
                    html = html + '</div>';
                });

                return html;
            }
        });

        return $.mage.configurable;
    }
});
