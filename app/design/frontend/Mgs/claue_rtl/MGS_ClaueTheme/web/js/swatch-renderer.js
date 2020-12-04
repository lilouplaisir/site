define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/smart-keyboard-handler',
    'mage/translate',
    'priceUtils',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'mage/validation/validation',
    'zoom-images',
    'mgs/slick', 
    'mgs/owlcarousel',
    'mage/translate',
    'magnificPopup'
], function ($) {
    'use strict';

    return function (widget) {

        $.widget('mage.SwatchRenderer', widget, {
            /**
             * @private
             */
            _init: function () {
                if (_.isEmpty(this.options.jsonConfig.images)) {
                    this.options.useAjax = true;
                    // creates debounced variant of _LoadProductMedia()
                    // to use it in events handlers instead of _LoadProductMedia()
                    this._debouncedLoadProductMedia = _.debounce(this._LoadProductMedia.bind(this), 500);
                }

                if (this.options.jsonConfig !== '' && this.options.jsonSwatchConfig !== '') {
                    // store unsorted attributes
                    this.options.jsonConfig.mappedAttributes = _.clone(this.options.jsonConfig.attributes);
                    this._sortAttributes();
                    this._RenderControls();
                    this._setPreSelectedGallery();
                    $(this.element).trigger('swatch.initialized');
                } else {
                    console.log('SwatchRenderer: No input data received');
                }
                this.options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();
                
                var currentImages = [];   
                
                $(".product.media .item-image").each(function( index ) {
                    var item = [];
                    var url_video = "";
                    var type = 'image';
                    
                    if($(this).find('.popup-youtube').length){
                        url_video = $(this).find('.popup-youtube').attr('href');
                    }else if($(this).find('.lb.video-link').length){
                        url_video = $(this).find('.lb.video-link').attr('href');
                    }
                    if(url_video){
                        type = 'video';
                    }
                    
                    item['zoom'] = $(this).attr('data-zoom');
                    item['full'] = $(this).find('.img-responsive').attr('src');
                    item['thumb'] = $(this).find('.img-responsive').attr('src');
                    item['type'] = type;
                    item['videoUrl'] = url_video;
                    currentImages.push(item);
                });
                
                this.options.mediaGalleryInitial = currentImages;
            },
            /**
             * Callback for product media
             *
             * @param {Object} $this
             * @param {String} response
             * @private
             */
            _ProductMediaCallback: function ($this, response, isInProductView) {
                var $main = isInProductView ? $this.parents('.column.main') : $this.parents('.product-item-info'),
                    $widget = this,
                    images = [],

                    /**
                     * Check whether object supported or not
                     *
                     * @param {Object} e
                     * @returns {*|Boolean}
                     */
                    support = function (e) {
                        return e.hasOwnProperty('large') && e.hasOwnProperty('medium') && e.hasOwnProperty('small');
                    };

                if (_.size($widget) < 1 || !support(response)) {
                    this.updateBaseImage(this.options.mediaGalleryInitial, $main, isInProductView);

                    return;
                }
                
                if(response.media_type == 'external-video') {
                    response.media_type = 'video';
                }
                
                images.push({
                    full: response.large,
                    img: response.medium,
                    thumb: response.small,
                    zoom: response.zoom,
                    type: response.media_type,
                    videoUrl: response.video_url,
                    isMain: true
                });

                if (response.hasOwnProperty('gallery')) {
                    $.each(response.gallery, function () {
                        if (!support(this) || response.large === this.large) {
                            return;
                        }
                        
                        if(this.media_type == 'external-video') {
                            this.media_type = 'video';
                        }
                        
                        images.push({
                            full: this.large,
                            img: this.medium,
                            zoom: this.zoom,
                            thumb: this.small,
                            type: this.media_type,
                            videoUrl: this.video_url
                        });
                    });
                }

                this.updateBaseImage(images, $main, isInProductView);
            },
            /**
             * Load media gallery using ajax or json config.
             *
             * @param {String|undefined} eventName
             * @private
             */
            _loadMedia: function (eventName) {
                var $main = this.inProductList ?
                        this.element.parents('.product-item-info') :
                        this.element.parents('.column.main'),
                    images;

                if (this.options.useAjax) {
                    this._debouncedLoadProductMedia();
                }  else {
                    images = this.options.jsonConfig.images[this.getProduct()];

                    if (!images) {
                        images = this.options.mediaGalleryInitial;
                    }

                    this.updateBaseImage(images, $main, !this.inProductList, eventName);
                }
            },
            /**
             * Update [gallery-placeholder] or [product-image-photo]
             * @param {Array} images
             * @param {jQuery} context
             * @param {Boolean} isProductViewExist
             */
            updateBaseImage: function (images, context, isProductViewExist) {
                var justAnImage = images[0],
                    updateImg,
                    imagesToUpdate,
                    gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                    zoomimg = $('#zoom_image').val(),
                    glr_layout = $('#glr_layout').val(),
                    lbox_image = $('#lbox_image').val(),
                    item;

                if (isProductViewExist) {
                    imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];

                    if (this.options.onlyMainImg) {
                        updateImg = imagesToUpdate.filter(function (img) {
                            return img.isMain;
                        });
                        item = updateImg.length ? updateImg[0] : imagesToUpdate[0];
                        gallery.updateDataByIndex(0, item);

                        gallery.seek(1);
                    } else {
                        if(imagesToUpdate.length == 1){
                            this.updateOneImage(imagesToUpdate);
                        }else{
                            if(glr_layout == 2){
                                this.updateBaseImageList(imagesToUpdate);
                            }else if(glr_layout == 4){
                                this.updateBaseImageVertical(imagesToUpdate);
                            }else if(glr_layout == 0){
                                this.updateBaseImageHorizontal(imagesToUpdate);
                            }else if(glr_layout == 1){
                                this.updateGalleryGrid(imagesToUpdate);
                            }else if(glr_layout == 5){
                                this.updateBaseImageOwl(imagesToUpdate);
                            }
                        }
                    }
                    
                    if(zoomimg == 1){
                        this.zoomImage();
                    }
                    
                    if(lbox_image == 1){
                        this.lightBoxGallery();
                    }
                    
                    this.videoPopup();
                    
                } else if (justAnImage && justAnImage.img) {
                    context.find('.product-image-photo').attr('src', justAnImage.img);
                }
            },
            
            videoPopup: function(){
                $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
                    type: 'iframe',
                    preloader: false,
                    mainClass: 'mfp-img-gallery',
                    fixedContentPos: true
                });
            },
            
            updateBaseImageOwl: function(imagesToUpdate) {
                var img_change = "";
                var view_type = $('#view_type').val();
                
                img_change = '<div id="owl-carousel-gallery" class="owl-carousel gallery-5">'+this.generateHtmlImage(imagesToUpdate)+'</div>';
                
                $(".product.media").html(img_change);
                
                if(view_type == 'quickview') {
                    $('#owl-carousel-gallery').on(' initialized.owl.carousel', function(event) {
                        setTimeout(function(){ 
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
                    navText: [$.mage.__('Prev'),$.mage.__('Next')],
                    rtl: true,
                });
                
                if(view_type == 'quickview') {
                    $('#owl-carousel-gallery').on('resized.owl.carousel', function(event) {
                        setTimeout(function(){ 
                            var hs = $('#owl-carousel-gallery').height();
                            $('.product-info-main').height(hs);
                        }, 100);
                    });
                }
            },
            
            updateBaseImageList: function(imagesToUpdate) {
                var img_change = "";
                
                img_change = '<div class="gallery-list">'+this.generateHtmlImage(imagesToUpdate)+'</div>';
                
                $(".product.media").html(img_change);
            },
            
            updateGalleryGrid: function(imagesToUpdate) {
                var img_change = "";
                var view_type = $('#view_type').val();
                
                img_change += '<div class="row">';
                   var count = 0;
                   $.each(imagesToUpdate, function(index) {
                       count++;
                       img_change += '<div class="item col-xs-6">';
                        if(lbox_image == 1){
                            var href = imagesToUpdate[index].zoom;
                            var cla = 'lb';
                            if(imagesToUpdate[index].type == 'video' && imagesToUpdate[index].videoUrl != ""){
                                href = imagesToUpdate[index].videoUrl;
                                cla = 'lb video-link';
                            }
                            img_change = img_change + '<div class="product item-image imgzoom" data-zoom="'+imagesToUpdate[index].zoom+'"><a href="'+href+'" class="'+cla+'"><img class="img-responsive" src="'+imagesToUpdate[index].full+'" alt=""/></a></div>';
                        }else {
                            img_change = img_change + '<div class="product item-image imgzoom" data-zoom="'+imagesToUpdate[index].zoom+'"><img class="img-responsive" src="'+imagesToUpdate[index].full+'" alt=""/>';
                            if(imagesToUpdate[index].type == 'video' && imagesToUpdate[index].videoUrl != ""){
                                img_change = img_change + '<a target="_blank" class="popup-youtube btn btn-primary" href="'+imagesToUpdate[index].videoUrl+'">'+$.mage.__('Watch Video')+'</a>';
                            }
                            img_change = img_change + '</div>';
                        }
                        if(count % 2 == 0){
                            img_change += '<div class="clearfix"></div>';
                        }
                        img_change += '</div>';
                    }); 
                 img_change += '</div>';
                
                $(".product.media").html(img_change);
            },
            
            updateBaseImageHorizontal: function(imagesToUpdate) {
                var img_change = "";
                
                img_change = '<div id="owl-carousel-gallery" class="owl-carousel gallery-horizontal">'+this.generateHtmlImage(imagesToUpdate)+'</div>';
                
                img_change = img_change + '<div id="horizontal-thumbnail" class="owl-carousel horizontal-thumbnail">'+this.generateHtmlThumb(imagesToUpdate)+'</div>';
                
                $(".product.media").html(img_change);
                
                $('#owl-carousel-gallery').owlCarousel({
                    items: 1,
                    autoplay: false,
                    lazyLoad: false,
                    nav: true,
                    dots: false,
                    navText: ["<span class='fa fa-angle-left'></span>","<span class='fa fa-angle-right'></span>"],
                    rtl: true
                });
                
                $('#owl-carousel-gallery').on('changed.owl.carousel', function(event) {
                    var index = event.item.index;
                    $('#horizontal-thumbnail .item-thumb').removeClass('active');
                    $('#horizontal-thumbnail .item-thumb[data-owl='+index+']').addClass('active');
                    $('#horizontal-thumbnail').trigger('to.owl.carousel', index);
                });
                
                $('#horizontal-thumbnail').owlCarousel({
                    items: 4,
                    autoplay: false,
                    lazyLoad: false,
                    nav: true,
                    dots: false,
                    rtl: true,
                    navText: ["<span class='fa fa-angle-left'></span>","<span class='fa fa-angle-right'></span>"],
                    responsive:{
                        0:{
                            items:2
                        },
                        480:{
                            items:2
                        },
                        768:{
                            items:3
                        },
                        992:{
                            items:4
                        }
                    }
                });
                
                $('#horizontal-thumbnail .item-thumb').click(function(){
                    $('#horizontal-thumbnail .item-thumb').removeClass('active');
                    var position = $(this).attr('data-owl');
                    $('#owl-carousel-gallery').trigger('to.owl.carousel', position);
                    $(this).addClass('active');
                });
                
            },
            
            updateBaseImageVertical: function(imagesToUpdate) {
                var img_change = "";
                
                img_change = '<div class="vertical-gallery">';
                    
                    img_change = img_change + '<div id="vertical-thumbnail-wrapper"><div id="vertical-thumbnails" class="vertical-thumbnail">'+this.generateHtmlThumb(imagesToUpdate)+'</div></div>';
                    
                    img_change = img_change + '<div id="owl-carousel-gallery" class="owl-carousel gallery-vertical">'+this.generateHtmlImage(imagesToUpdate)+'</div>';
                    
                img_change = img_change + '</div>';
                
                $(".product.media").html(img_change);
                
                $('#owl-carousel-gallery').on('initialized.owl.carousel', function(event) {
                    setTimeout(function(){
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
                    navText: ["<span class='fa fa-angle-left'></span>","<span class='fa fa-angle-right'></span>"],
                    rtl: true
                });
                
                $('#vertical-thumbnails img').load(function(){
                    setTimeout(function(){
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
                
                $('#owl-carousel-gallery').on('changed.owl.carousel', function(event) {
                    var index = event.item.index;
                    $('#vertical-thumbnails .item-thumb').removeClass('active');
                    $('#vertical-thumbnails .item-thumb[data-owl='+index+']').addClass('active');
                    var wdw = $(window).width();
                    var ci = imagesToUpdate.length;
                    if(wdw >= 1199 && ci > 3) {
                        $('#vertical-thumbnails').slick('slickGoTo', index);
                    }else if(wdw < 1199 && wdw >= 768 && ci > 2){
                        $('#vertical-thumbnails').slick('slickGoTo', index);
                    }else if(wdw < 768 && wdw >= 600 && ci > 3){
                        $('#vertical-thumbnails').slick('slickGoTo', index);
                    }else if(wdw < 768 && wdw >= 600 && ci > 2){
                        $('#vertical-thumbnails').slick('slickGoTo', index);
                    }else if(wdw < 360){
                        $('#vertical-thumbnails').slick('slickGoTo', index);
                    }
                });
                
                $('#owl-carousel-gallery').on('resized.owl.carousel', function(event) {
                    var hs = $('#owl-carousel-gallery').height();
                    $('.product.media').height(hs);
                });
                
                $('#vertical-thumbnails .item-thumb').click(function(){
                    $('#vertical-thumbnails .item-thumb').removeClass('active');
                    var position = $(this).attr('data-owl');
                    $('#owl-carousel-gallery').trigger('to.owl.carousel', position);
                    $(this).addClass('active');
                });
            },
            
            updateOneImage: function(imagesToUpdate) {
                var img_change = "",
                    lbox_image = $('#lbox_image').val();
                if(lbox_image == 1){
                    var href = imagesToUpdate[0].zoom;
                    var cla = 'lb';
                    if(imagesToUpdate[0].type == 'video' && imagesToUpdate[0].videoUrl != ""){
                        href = imagesToUpdate[0].videoUrl;
                        cla = 'lb video-link';
                    }
                    img_change = img_change + '<div class="product single-image item-image base-image imgzoom" data-zoom="'+imagesToUpdate[0].zoom+'"><a href="'+href+'" class="'+cla+'"><img class="img-responsive" src="'+imagesToUpdate[0].full+'" alt=""/></a></div>';
                }else {
                    img_change = img_change + '<div class="product single-image item-image base-image imgzoom" data-zoom="'+imagesToUpdate[0].zoom+'"><img class="img-responsive" src="'+imagesToUpdate[0].full+'" alt=""/>';
                    if(imagesToUpdate[0].type == 'video' && imagesToUpdate[0].videoUrl != ""){
                        img_change = img_change + '<a target="_blank" class="popup-youtube btn btn-primary" href="'+imagesToUpdate[0].videoUrl+'">'+$.mage.__('Watch Video')+'</a>';
                    }
                    img_change = img_change + '</div>';
                }
                
                $(".product.media").height('auto');
                $(".product.media").html(img_change);
            },
            
            zoomImage: function(){
                $(".imgzoom").each(function( index ) {
                    zoomElement(this);
                });
            },
            
            lightBoxGallery: function(){
                $('.product.media').magnificPopup({
                    delegate: '.imgzoom .lb',
                    type: 'image',
                    tLoading: 'Loading image #%curr%...',
                    mainClass: 'mfp-img-gallery',
                    fixedContentPos: true,
                    gallery: {
                        enabled: true,
                        navigateByImgClick: true,
                        preload: [0,1]
                    },
                    iframe: {
                        markup: '<div class="mfp-figure">'+
                            '<div class="mfp-close"></div>'+
                            '<div class="mfp-img"></div>'+
                            '<div class="mfp-bottom-bar">'+
                              '<div class="mfp-title"></div>'+
                              '<div class="mfp-counter"></div>'+
                            '</div>'+
                          '</div>',
                    },
                    image: {
                        tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
                    },
                    callbacks: {
                        elementParse: function(item) {
                            if(item.el.context.className == 'lb video-link') {
                                item.type = 'iframe';
                            } else {
                                item.type = 'image';
                            }
                        }
                    }
                });
            },
            
            generateHtmlImage: function(imagesToUpdate){
                var html = "",
                    lbox_image = $('#lbox_image').val();
                $.each(imagesToUpdate, function(index) {
                    if(lbox_image == 1){
                        var href = imagesToUpdate[index].zoom;
                        var cla = 'lb';
                        if(imagesToUpdate[index].type == 'video' && imagesToUpdate[index].videoUrl != ""){
                            href = imagesToUpdate[index].videoUrl;
                            cla = 'lb video-link';
                        }
                        html = html + '<div class="product item-image imgzoom" data-zoom="'+imagesToUpdate[index].zoom+'"><a href="'+href+'" class="'+cla+'"><img class="img-responsive" src="'+imagesToUpdate[index].full+'" alt=""/></a></div>';
                    }else {
                        html = html + '<div class="product item-image imgzoom" data-zoom="'+imagesToUpdate[index].zoom+'"><img class="img-responsive" src="'+imagesToUpdate[index].full+'" alt=""/>';
                        if(imagesToUpdate[index].type == 'video' && imagesToUpdate[index].videoUrl != ""){
                            html = html + '<a target="_blank" class="popup-youtube btn btn-primary" href="'+imagesToUpdate[index].videoUrl+'">'+$.mage.__('Watch Video')+'</a>';
                        }
                        html = html + '</div>';
                    }
                });
                return html;
            },
            
            generateHtmlThumb: function(imagesToUpdate){
                var html = "",
                    lbox_image = $('#lbox_image').val();
                    
                $.each(imagesToUpdate, function(index) {
                    var classth = 'item-thumb';
                    if(index == 0){ classth = 'item-thumb active'; }
                    
                    html = html + '<div class="'+classth+'" data-owl="'+index+'"><img class="img-responsive" src="'+imagesToUpdate[index].thumb+'" alt=""/>';
                        if(lbox_image != 1) {
                            if(imagesToUpdate[index].type == 'video' && imagesToUpdate[index].videoUrl != ""){
                                html = html + '<a target="_blank" class="popup-youtube" href="'+imagesToUpdate[index].videoUrl+'"></a>';
                            }
                        }
                    html = html + '</div>';
                });
                
                return html;
            }
        });

        return $.mage.SwatchRenderer;
    }
});