define([
    'jquery',
    'productGallery',
    'mage/template',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation',
    'newImgixDialog',
    'Magento_Catalog/js/product-gallery'
], function ($, productGallery,mageTemplate) {
    'use strict';

    /**
     * Formats incoming bytes value to a readable format.
     *
     * @param {Number} bytes
     * @returns {String}
     */
     function bytesToSize(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'],
            i;

        if (bytes === 0) {
            return '0 Byte';
        }

        i = window.parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));

        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    }

    $.widget('mage.productGallery', productGallery, {

        /**
         * Bind events
         * @private
         */
        _bind: function () {
            var events = {},
                itemId;
            /**
             * Add item_id value to opened modal
             * @param {Object} event
             */
            events['click ' + this.options.imageSelector] = function (event) {
                if (!$(event.currentTarget).is('.ui-sortable-helper')) {
                    itemId = $(event.currentTarget).find('input')[0].name.match(/\[([^\]]*)\]/g)[2];
                    this.imgixDialog.find('#item_id').val(itemId);
                }
            };
            this._on(events);
            this.element.prev().find('[data-role="add-imgix-button"]').on('click', this.showModal.bind(this));
            this.element.on('openDialog', '.gallery.ui-sortable', $.proxy(this._onOpenDialog, this));
        },

        /**
         * @private
         */
        _create: function () {
            this.options.types = this.options.types || this.element.data('types');
            this.options.images = this.options.images || this.element.data('images');
            this.options.parentComponent = this.options.parentComponent || this.element.data('parent-component');

            this.imgTmpl = mageTemplate(this.element.find(this.options.template).html().trim());

            $.each(this.options.images, $.proxy(function (index, imageData) {
                this._addItem(imageData);
            }, this));
            this._super();
            this.imgixDialog = this.element.find('#new-imgix-image');
            this.imgixDialog.mage('newImgixDialog', this.imgixDialog.data('modalInfo'));
        },

        /**
         * Add image items
         */
        _addItem: function (imageData) {
            var element,
                imgElement,
                lastElement,
                count,
                position;

            if (this._isInitializingItems) {
                count = this._initializedItemCount++;
                lastElement = this._lastInitializedElement;
            } else {
                count = this.element.find(this.options.imageSelector).length;
                lastElement = this.element.find(this.options.imageSelector + ':last');
            }

            position = count + 1;

            if (lastElement && lastElement.length === 1) {
                position = parseInt(lastElement.data('imageData').position || count, 10) + 1;
            }
            imageData = $.extend({
                'file_id': imageData['value_id'] ? imageData['value_id'] : Math.random().toString(33).substr(2, 18),
                'disabled': imageData.disabled ? imageData.disabled : 0,
                'position': position,
                sizeLabel: bytesToSize(imageData.size)
            }, imageData);

            element = this.imgTmpl({
                data: imageData
            });

            element = $(element).data('imageData', imageData);

            if (count === 0) {
                element.prependTo(this.element);
            } else {
                element.insertAfter(lastElement);
            }

            this._lastInitializedElement = element;

            if (!this.options.initialized &&
                this.options.images.length === 0 ||
                this.options.initialized &&
                this.element.find(this.options.imageSelector + ':not(.removed)').length === 1
            ) {
                this.setBase(imageData);
            }

            imgElement = element.find(this.options.imageElementSelector);

            imgElement.on('load', this._updateImageDimesions.bind(this, element));

            $.each(this.options.types, $.proxy(function (index, image) {
                if (imageData.file === image.value) {
                    this.element.trigger('setImageType', {
                        type: image.code,
                        imageData: imageData
                    });
                }
            }, this));

            if (!this._isInitializingItems) {
                this._updateImagesRoles();
                this._contentUpdated();
            }
        },
        /**
         * Open dialog for external video
         * @private
         */
        _onOpenDialog: function (e, imageData) {
            
            if (imageData['media_type'] !== 'external-video') {
                this._superApply(arguments);
            } else {
                this.showModal();
            }
        },

        /**
         * Fired on trigger "openModal"
         */
        showModal: function () {
            
            var page_action = $('.mage-new-imgix-dialog .page-main-actions');
            $('.mage-new-imgix-dialog .modal-content .page-action-btn').after(page_action);
            
            $('#imgix_search_keyword').val('');
            $('.load-more-imgix-image').attr('data-next-page','0');
            $('.load-more-imgix-image').attr('data-current','0');
            $('.load-more-imgix-image').hide();
            
            $('.imgix-error-message').html('');
            var firstSource = null;
            // On imgix modal open get imgix images for catalog
            if ($(".source-selector-menu li").length){
                firstSource = $(".source-selector-menu li").first().attr("data-source-id");
                var source_name = $(".source-selector-menu li").first().find(".source-name").text();
                $("#current-selected-source").text(source_name);
            } else {
                firstSource = 0;
                $("#current-selected-source").text('No sources');
            }   
            $("#current-selected-source").attr('current_selected_source',firstSource);
            
            var keyword = $('#imgix_search_keyword').val();
            var cursor = $(".load-more-imgix-image").attr('data-next-page');

            $.ajax({ 	
                showLoader: true, 
                url: window.catalogImgixImageUrl, 
                data: {sourceId: firstSource,keyword: keyword, cursor: cursor},
                type: "POST", 
                dataType: 'json'
            }).done(function (response) { 
                if(response.isNoImages == true){
                    $('.assets').addClass("no-imgix-image");
                }
                if(response.isNoImages == false){
                    if($('.assets').hasClass("no-imgix-image")){
                        $('.assets').removeClass("no-imgix-image");
                    }
                }
                $('.assets').html(response.html).trigger('contentUpdated');
                if(response.hasMore == true){
                    $('.load-more-imgix-image').show();
                    $('.load-more-imgix-image').attr('data-next-page',response.next);
                }
                if(response.hasMore == false){
                    $('.load-more-imgix-image').hide();
                    $('.load-more-imgix-image').attr('data-next-page','');
                }
                if(response.current){
                    $('.load-more-imgix-image').attr('data-current',response.current);
                }

                if(response.isError == true){
                    $('.imgix-error-message').html(response.errorMessage);
                }
            });

            this.imgixDialog.modal('openModal');
        }
    });

    return $.mage.productGallery;
});
