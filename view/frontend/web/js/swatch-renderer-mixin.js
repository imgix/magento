define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/smart-keyboard-handler',
    'mage/translate',
    'priceUtils',
    'ImgixClient',
    'jquery-ui-modules/widget',
    'jquery/jquery.parsequery',
    'mage/validation/validation'
], function ($, _, mageTemplate, keyboardHandler, $t, priceUtils,ImgixClient) {
    'use strict';

    return function(widget) {
        $.widget('mage.SwatchRenderer', widget, {
            
            /**
             * Update [gallery-placeholder] or [product-image-photo]
             * @param {Array} images
             * @param {jQuery} context
             * @param {Boolean} isInProductView
             */
            updateBaseImage: function (images, context, isInProductView) {
                var justAnImage = images[0],
                    initialImages = this.options.mediaGalleryInitial,
                    imagesToUpdate,
                    gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                    isInitial;

                if (isInProductView) {
                    if (_.isUndefined(gallery)) {
                        context.find(this.options.mediaGallerySelector).on('gallery:loaded', function () {
                            this.updateBaseImage(images, context, isInProductView);
                        }.bind(this));

                        return;
                    }

                    imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                    isInitial = _.isEqual(imagesToUpdate, initialImages);

                    if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                        imagesToUpdate = imagesToUpdate.concat(initialImages);
                    }

                    imagesToUpdate = this._setImageIndex(imagesToUpdate);

                    gallery.updateData(imagesToUpdate);
                    this._addFotoramaVideoEvents(isInitial);
                } else if (justAnImage && justAnImage.img) {
                    context.find('.product-image-photo').removeAttr('srcset');
                    var url = justAnImage.img;
                    if(url.length){
                        var params = null; 
                        var sourceDomain = null; 
                        var domain = null; 
                        var imageName = null;
                        var options = null;
                        var urlParams = null;
                        
                        // Check url has ? or not 
                        if (url.match(/\?./)) {
                            params = url.split('?');
                            // Get soucedomain from url
                            url = params[0] ;
                            // Get params from imgix image url
                            urlParams = params[1];
                        }
                        // Get source domain and image name from url
                        if (url.indexOf('imgix.net') > -1)
                        {
                            // Get imgix subdomain from the url
                            sourceDomain = url.substr(0,url.indexOf('imgix.net') + 9 );
                            // Remove https from imgix subdomain
                            domain = sourceDomain.slice(8);
                            // Get image name from url
                            imageName = url.substring(url.indexOf('imgix.net/') + 9);
                            
                            const client = new ImgixClient({
                                domain: domain,
                            });

                            var paramsJson = {};

                            if (urlParams !== null){
                                // Get parameters after ? from url
                                options = params[1].split('&');
                                // Start create params json object
                                $.each(options, function( index, value ) {
                                    var tmp = value.split('=');
                                    var key = tmp[0]; 
                                    var val = tmp[1]; 
                                    paramsJson[key] = val;
                                });
                                // End create params json object
                                if(paramsJson){
                                    var srcset = client.buildSrcSet(imageName,paramsJson);
                                    context.find('.product-image-photo').attr('srcset', srcset);
                                } else {
                                    var srcset = client.buildSrcSet(imageName);
                                    context.find('.product-image-photo').attr('srcset', srcset);
                                }
                            } else {
                                var srcset = client.buildSrcSet(imageName);
                                context.find('.product-image-photo').attr('srcset', srcset);
                            }
                        }
                    }
                    context.find('.product-image-photo').attr('src', justAnImage.img);
                }
            },
        });
        return $.mage.SwatchRenderer;
    };
});