define([
    'jquery',
    'productGallery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation',
    'newImgixDialog'
], function ($, productGallery) {
    'use strict';

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
            window.reloadImages = false;
            this._super();
            this.imgixDialog = this.element.find('#new-imgix-image');
            this.imgixDialog.mage('newImgixDialog', this.imgixDialog.data('modalInfo'));
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
