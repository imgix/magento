define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/image-uploader',
    'Magento_PageBuilder/js/resource/resize-observer/ResizeObserver',
    'Magento_PageBuilder/js/events',
    'mage/translate',
    'mage/adminhtml/browser',
    'mageUtils',
], function ($, _, uiRegistry, Uploader, ResizeObserver, events, $t,browser, utils) {
    'use strict';

    var initializedOnce = false;

    var mixin = {
        defaults: {
            $uploadArea: null,
            isShowImageUploadInstructions: true,
            isShowImageUploadOptions: false,
            visibleControls: true,
            classes: {
                dragging: 'dragging',
                draggingInside: 'dragging-inside',
                draggingOutside: 'dragging-outside'
            },
            // listed in ascending order
            elementWidthModifierClasses: {
                '_micro-ui': {
                    maxWidth: 130
                },
                '_compact-ui': {
                    minWidth: 131,
                    maxWidth: 440
                }
            },
            translations: {
                allowedFileTypes: $t('Allowed file types'),
                dragImageHere: $t('Drag image here'),
                dropHere: $t('Drop here'),
                maximumFileSize: $t('Maximum file size'),
                selectFromGallery: $t('Select from Gallery'),
                or: $t('or'),
                uploadImage: $t('Upload Image'),
                uploadNewImage: $t('Upload New Image'),
                addImgixImage: $t('Add imgix Image'),
                selectSourceImage: $t('Select image from sources')
            },
            tracks: {
                visibleControls: true
            }
        },

        /**
         * Assign uid for media gallery
         *
         * @return {ImageUploader} Chainable.
         */
         initConfig: function () {
            var mediaGalleryImgixUid = utils.uniqueid();

            this._super();

            _.extend(this, {
                mediaGalleryImgixUid: mediaGalleryImgixUid
            });

            return this;
        },

        /**
         * Add file event callback triggered from media gallery
         *
         * @param {ImageUploader} imageUploader - UI Class
         * @param {Event} e
         */
        addFileFromMediaGallery: function (imageUploader, e) {
            var $buttonEl = $(e.target),
                fileSize = $buttonEl.data('size'),
                fileMimeType = $buttonEl.data('mime-type'),
                filePathname = $buttonEl.val(),
                fileBasename = filePathname.split('/').pop();

            this.addFile({
                type: fileMimeType,
                name: fileBasename,
                size: fileSize,
                url: filePathname
            });
        },

        /**
         * Open the media browser dialog
         *
         * @param {ImageUploader} imageUploader - UI Class
         * @param {Event} e
         */
         openImgixImageDialog: function (imageUploader, e) {      
                
            var $buttonEl = $(e.target),
                openDialogUrl = this.mediaGallery.openDialogUrl +
                'target_element_id/' + $buttonEl.attr('id') +
                '/store/' + this.mediaGallery.storeId +
                '/type/image/?isAjax=true';
            if (this.mediaGallery.initialOpenSubpath) {
                openDialogUrl += '&current_tree_path=' + Base64.idEncode(this.mediaGallery.initialOpenSubpath);
            }
            browser.openImgixDialog(
                openDialogUrl,
                null,
                null,
                this.mediaGallery.openDialogTitle,
                {
                    targetElementId: $buttonEl.attr('id')
                }
            );
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
