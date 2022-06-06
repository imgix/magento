 define([
    'jquery',
    'underscore',
    'productGallery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation'
], function ($, _, productGallery) {
    'use strict';

    /**
     */
    $.widget('mage.newImgixDialog', {

        _previewImage: null,

        clickedElement: '',

        _images: {},

        _imageTypes: [
            '.jpeg',
            '.pjpeg',
            '.jpeg',
            '.jpg',
            '.pjpeg',
            '.png',
            '.gif'
        ],

        _imageProductGalleryWrapperSelector: '#image-container',

        _videoPreviewInputSelector: '#new_imgix_screenshot',

        _videoPreviewRemoteSelector: '',

        _videoDisableinputSelector: '#new_video_disabled',

        _videoPreviewImagePointer: '#new_video_screenshot_preview',

        _videoFormSelector: '#new_imgix_form',

        _itemIdSelector: '#item_id',

        _videoUrlSelector: '[name="video_url"]',

        _videoImageFilenameselector: '#file_name',

        _videoUrlWidget: null,

        _videoInformationBtnSelector: '[name="new_video_get"]',

        _editVideoBtnSelector: '.image',

        _deleteGalleryVideoSelector: '[data-role=delete-button]',

        _deleteGalleryVideoSelectorBtn: null,

        _videoInformationGetBtn: null,

        _videoInformationGetUrlField: null,

        _videoInformationGetEditBtn: null,

        _isEditPage: false,

        _onlyVideoPlayer: false,

        _tempPreviewImageData: null,

        _videoPlayerSelector: '.mage-new-imgix-dialog',

        _videoRequestComplete: null,

        _gallery: null,

        /**
         * Bind events
         * @private
         */
        _bind: function () {
            var events = {
                'setImage': '_onSetImage'
            };

            this._on(events);

            this._videoInformationGetBtn = this.element.find(this._videoInformationBtnSelector);
            this._videoInformationGetUrlField = this.element.find(this._videoUrlSelector);
            this._videoInformationGetEditBtn = this._gallery.find(this._editVideoBtnSelector);

            this._videoInformationGetBtn.on('click', $.proxy(this._onGetVideoInformationClick, this));
            this._videoInformationGetUrlField.on('focusout', $.proxy(this._onGetVideoInformationFocusOut, this));
        },

        /**
         * Remove ".tmp"
         * @param {String} name
         * @returns {*}
         * @private
         */
         __prepareFilename: function (name) {
            var tmppost = '.tmp';

            if (!name) {
                return name;
            }

            if (name.endsWith(tmppost)) {
                name = name.slice(0, name.length - tmppost.length);
            }

            return name;
        },

        /**
         * Set image data
         * @param {String} file
         * @param {Object} imageData
         * @private
         */
         _setImage: function (file, imageData) {
            file = this.__prepareFilename(file);
            this._images[file] = imageData;
            this._gallery.trigger('addItem', imageData);
            this.element.trigger('setImage', imageData);
            this._addVideoClass(imageData.url);
        },

        /**
         * Get image data
         *
         * @param {String} file
         * @returns {*}
         * @private
         */
         _getImage: function (file) {
            file = this.__prepareFilename(file);

            return this._images[file];
        },

        /**
         * Replace image (update)
         * @param {String} oldFile
         * @param {String} newFile
         * @param {Object} imageData
         * @private
         */
         _replaceImage: function (oldFile, newFile, imageData) {
            var tmpNewFile = newFile,
                tmpOldImage,
                newImageId,
                oldNewFilePosition,
                fc,
                suff,
                searchsuff,
                key,
                oldValIdElem;

            oldFile = this.__prepareFilename(oldFile);
            newFile = this.__prepareFilename(newFile);
            tmpOldImage = this._images[oldFile];

            if (newFile === oldFile) {
                this._images[newFile] = imageData;
                this.saveImageRoles(imageData);
                this._updateVisibility(imageData);
                this._updateImageTitle(imageData);

                return null;
            }

            this._removeImage(oldFile);
            this._setImage(newFile, imageData);

            if (!oldFile || !imageData.oldFile) {
                return null;
            }

            newImageId = this.findElementId(tmpNewFile);
            fc = this.element.find(this._itemIdSelector).val();

            suff = 'product[media_gallery][images]' + fc;

            searchsuff = 'input[name="' + suff + '[value_id]"]';
            key = this._gallery.find(searchsuff).val();

            if (!key) {
                return null;
            }

            oldValIdElem = document.createElement('input');
            this._gallery.find('form[data-form="edit-product"]').append(oldValIdElem);
            $(oldValIdElem).attr({
                type: 'hidden',
                name: 'product[media_gallery][images][' + newImageId + '][save_data_from]'
            }).val(key);

            oldNewFilePosition = parseInt(tmpOldImage.position, 10);
            imageData.position = oldNewFilePosition;

            this._gallery.trigger('setPosition', {
                imageData: imageData,
                position: oldNewFilePosition
            });
        },

        /**
         * Remove image data
         * @param {String} file
         * @private
         */
        _removeImage: function (file) {
            var imageData = this._getImage(file);

            if (!imageData) {
                return null;
            }

            this._gallery.trigger('removeItem', imageData);
            this.element.trigger('removeImage', imageData);
            delete this._images[file];
        },

        /**
         * Fired when image setted
         * @param {Event} event
         * @param {Object} imageData
         * @private
         */
         _onSetImage: function (event, imageData) {
            this.saveImageRoles(imageData);
        },
        
        /**
         *
         * Wrap _uploadFile
         * @param {String} file
         * @param {String} oldFile
         * @param {Function} callback
         * @private
         */
        _uploadImage: function (file, oldFile, callback) {
            var url = this.options.saveVideoUrl,
            data = {
                files: file,
                url: url
            };

            this._blockActionButtons(true, true);
            this._uploadFile(data, $.proxy(function (result) {
                this._onImageLoaded(result, file, oldFile, callback);
                this._blockActionButtons(false);
            }, this));

        },

        /**
         * @param {String} result
         * @param {String} file
         * @param {String} oldFile
         * @param {Function} callback
         * @private
         */
        _onImageLoaded: function (result, file, oldFile, callback) {
            var data;

            try {
                data = JSON.parse(result);
            } catch (e) {
                data = result;
            }

            if (this.element.find('#video_url').parent().find('.image-upload-error').length > 0) {
                this.element.find('.image-upload-error').remove();
            }

            if (data.errorcode || data.error) {
                this.element.find('#video_url').parent().append('<div class="image-upload-error">' +
                '<div class="image-upload-error-cross"></div><span>' + data.error + '</span></div>');

                return;
            }
            $.each(this.element.find(this._videoFormSelector).serializeArray(), function (i, field) {
                data[field.name] = field.value;
            });
            data.disabled = this.element.find(this._videoDisableinputSelector).attr('checked') ? 1 : 0;
            data['media_type'] = 'external-video';
            data.oldFile = oldFile;

            oldFile ?
                this._replaceImage(oldFile, data.file, data) :
                this._setImage(data.file, data);
            callback.call(0, data);
        },

        /**
         * File uploader
         * @private
         */
         _uploadFile: function (data, callback) {
            var fu = this.element.find(this._videoPreviewInputSelector),
                tmpInput = document.createElement('input'),
                fileUploader = null;

            $(tmpInput).attr({
                'name': fu.attr('name'),
                'value': fu.val(),
                'type': 'file',
                'data-ui-ud': fu.attr('data-ui-ud')
            }).css('display', 'none');
            fu.parent().append(tmpInput);
            fileUploader = $(tmpInput).fileupload();
            fileUploader.fileupload('send', data).done(function (result, textStatus, jqXHR) {
                tmpInput.remove();
                callback.call(null, result, textStatus, jqXHR);
            });
        },

        /**
         * Update style
         * @param {String} url
         * @private
         */
         _addVideoClass: function (url) {
            var classVideo = 'video-item';

            this._gallery.find('img[src="' + url + '"]').addClass(classVideo);
        },

        /**
         * Build widget
         * @private
         */
        _create: function () {
            var imgs = _.values(this.element.closest(this.options.videoSelector).data('images')) || [],
                $galleryContainer = $('#media_gallery_content'),
                widget,
                uploader,
                tmp,
                i;


            this._gallery =  this.element.closest(this.options.videoSelector);

            for (i = 0; i < imgs.length; i++) {
                tmp = imgs[i];
                this._images[tmp.file] = tmp;

                if (tmp['media_type'] === 'external-video') {
                    tmp.subclass = 'video-item';
                    this._addVideoClass(tmp.url);
                }
            }

            this._gallery.on('openDialog', $.proxy(this._onOpenDialog, this));
            this._bind();
            widget = this;
            uploader = this.element.find(this._videoPreviewInputSelector);
            uploader.on('change', this._onImageInputChange.bind(this));
            uploader.attr('accept', this._imageTypes.join(','));

            this.element.modal({
                type: 'slide',
                //appendTo: this._gallery,
                modalClass: 'mage-new-imgix-dialog form-inline',
                title: $.mage.__('New imgix Source Images'),
                buttons: [
                    {
                        text: $.mage.__('ADD IMAGE'),
                        class: 'action-primary image-upload-button',
                        click: $.proxy(widget._onCreate, widget)
                    },
                    {
                        text: $.mage.__('CANCEL'),
                        class: 'video-cancel-button',
                        click: $.proxy(widget._onCancel, widget)
                    },
                    {
                        text: $.mage.__('DELETE IMAGE'),
                        class: 'video-delete-button',
                        click: $.proxy(widget._onDelete, widget)
                    }
                ],

                /**
                 * @returns {null}
                 */
                opened: function () {
                    var roles,
                        file,
                        modalTitleElement,
                        imageData,
                        modal = widget.element.closest('.mage-new-imgix-dialog');

                    roles = widget.element.find('.video_image_role');
                    roles.prop('disabled', false);
                    file = widget.element.find('#file_name').val();
                    modalTitleElement = modal.find('.modal-title');
                    $("meta[name=viewport]").attr("content", "width=device-width, initial-scale=1.0");
                    if (!file) {
                        widget._blockActionButtons(true);

                        modal.find('.video-delete-button').hide();
                        modal.find('.video-edit').hide();
                        modal.find('.image-upload-button').show();
                        roles.prop('checked', widget._gallery.find('.image.item:not(.removed)').length < 1);
                        modalTitleElement.text($.mage.__('New imgix Source Images'));
                        widget._isEditPage = false;

                        return null;
                    }
                    widget._blockActionButtons(false);
                    modalTitleElement.text($.mage.__('Edit Video'));
                    widget._isEditPage = true;
                    imageData = widget._getImage(file);

                    if (!imageData) {
                        imageData = {
                            url: _.find(widget._gallery.find('.product-image'), function (image) {
                                return image.src.indexOf(file) > -1;
                            }).src
                        };
                    }

                    widget._onPreview(null, imageData.url, false);
                },

                /**
                 * Closed
                 */
                closed: function () {
                    $("meta[name=viewport]").attr("content", "width=1024");
                    widget._onClose();
                }
            });

            this.toggleButtons();
        },

        /**
         * @param {String} status
         * @private
         */
        _blockActionButtons: function (status) {
            this.element
                .closest('.mage-new-imgix-dialog')
                .find('.page-actions-buttons button.image-upload-button, .page-actions-buttons button.video-edit')
                .attr('disabled', status);
        },

        /**
         * Check form
         * @param {Function} callback
         */
        isValid: function (callback) {
            var videoForm = this.element.find(this._videoFormSelector),
                videoLoaded = true;

            this._blockActionButtons(true);

            this._videoUrlWidget.trigger('validate_video_url', $.proxy(function () {

                videoForm.mage('validation', {

                    /**
                     * @param {jQuery} error
                     * @param {jQuery} element
                     */
                    errorPlacement: function (error, element) {
                        error.insertAfter(element);
                    }
                }).on('highlight.validate', function () {
                    $(this).validation('option');
                });

                videoForm.validation();

                if (this._videoRequestComplete === false) {
                    videoLoaded = false;
                }

                callback(videoForm.valid() && videoLoaded);
            }, this));

            this._blockActionButtons(false);
        },

        /**
         * Fired when click on create video
         * @private
         */
        _onCreate: function () {
            var self = this;
            var images = [];
            var galleryContainer = $('#media_gallery_content');
            var img_width = '?w=150';

            $('.assets#contents').find('.imgix-asset.selected').each(function(){
                var img_url = $(this).find('img').attr('src')+img_width;
                var imageData = {
                    error: 0,
                    file: $(this).find('img').attr('src'),
                    name: "Image",
                    type: "image/jpeg",
                    url: $(this).find('img').attr('src'),
                    srcset: img_url
                }
                galleryContainer.trigger('addItem', imageData);
            });

            galleryContainer.trigger('resort');
            self.close();      
        },

        /**
         * Delegates call to producwt gallery to update video visibility.
         *
         * @param {Object} imageData
         */
         _updateVisibility: function () {
            this._gallery.trigger('updateVisibility', {
                disabled: imageData.disabled,
                imageData: imageData
            });
        },

        /**
         * Delegates call to product gallery to update video title.
         *
         * @param {Object} imageData
         */
         _updateImageTitle: function (imageData) {
            this._gallery.trigger('updateImageTitle', {
                imageData: imageData
            });
        },
        
        /**
         * Fired when clicked on delete
         * @private
         */
         _onDelete: function () {
            var filename = this.element.find(this._videoImageFilenameselector).val();

            this._removeImage(filename);
            this.close();
        },
        
        /**
         * Fired when clicked on cancel
         * @private
         */
        _onCancel: function () {
            $('.assets').empty();
            this.close();
        },

        /**
         *  Image file input handler
         * @private
         */
         _onImageInputChange: function () {
            var jFile = this.element.find(this._videoPreviewInputSelector),
                file = jFile[0],
                val = jFile.val(),
                prev = this._getPreviewImage(),
                ext = '.' + val.split('.').pop();

            if (!val) {
                return;
            }
            ext = ext ? ext.toLowerCase() : '';

            if (
                ext.length < 2 ||
                this._imageTypes.indexOf(ext.toLowerCase()) === -1 || !file.files || !file.files.length
            ) {
                prev.remove();
                this._previewImage = null;
                jFile.val('');

                return;
            } // end if
            file = file.files[0];
            this._tempPreviewImageData = null;
            this._onPreview(null, file, true);
        },

        /**
         * Change Preview
         * @param {String} error
         * @param {String} src
         * @param {Boolean} local
         * @private
         */
        _onPreview: function (error, src, local) {
            var img, renderImage;

            img = this._getPreviewImage();

            /**
             * Callback
             * @param {String} source
             */
            renderImage = function (source) {
                img.attr({
                    'src': source
                }).show();
            };

            if (error) {
                return;
            }

            if (!local) {
                renderImage(src);
            } else {
                this._readPreviewLocal(src, renderImage);
            }
        },

        /**
         *
         * Return preview image imstance
         * @returns {null}
         * @private
         */
        _getPreviewImage: function () {

            if (!this._previewImage) {
                this._previewImage = $(document.createElement('img')).css({
                    'width': '100%',
                    'display': 'none',
                    'src': ''
                });
                $(this._previewImage).insertAfter(this.element.find(this._videoPreviewImagePointer));
                $(this._previewImage).attr('data-role', 'video_preview_image');
            }

            return this._previewImage;
        },

        /**
         * Close slideout dialog
         */
        close: function () {
            $('.assets').empty();
            this.element.modal('closeModal');
        },

        /**
         * Close dialog wrap
         * @private
         */
        _onClose: function () {
            var newVideoForm;

            this._isEditPage = true;
            this.imageData = null;

            if (this._previewImage) {
                this._previewImage.remove();
                this._previewImage = null;
            }
            this._tempPreviewImageData = null;
            this.element.trigger('reset');
            newVideoForm = this.element.find(this._videoFormSelector);

            $(newVideoForm).find('input[type="hidden"][name!="form_key"]').val('');
            this._gallery.find(
                'input[name*="' + this.element.find(this._itemIdSelector).val() + '"]'
            ).parent().removeClass('active');

            try {
                newVideoForm.validation('clearError');
            } catch (e) {

            }
            newVideoForm.trigger('reset');
        },

        /**
         * Find element by fileName
         * @param {String} file
         */
        findElementId: function (file) {
            var elem = this._gallery.find('.image.item').find('input[value="' + file + '"]');

            if (!elem.length) {
                return null;
            }

            return $(elem).attr('name').replace('product[media_gallery][images][', '').replace('][file]', '');
        },

        /**
         * Save image roles
         * @param {Object} imageData
         */
        saveImageRoles: function (imageData) {
            var data = imageData.file,
                self = this,
                containers;

            if (data && data.length > 0) {
                containers = this._gallery.find('.image-placeholder').siblings('input');
                $.each(containers, function (i, el) {
                    var start = el.name.indexOf('[') + 1,
                        end = el.name.indexOf(']'),
                        imageType = el.name.substring(start, end),
                        imageCheckbox = self.element.find(
                            self._videoFormSelector + ' input[value="' + imageType + '"]'
                        );

                    self._changeRole(imageType, imageCheckbox.prop('checked'), imageData);
                });
            }
        },

        /**
         * Change image role
         * @param {String} imageType - role name
         * @param {bool} isEnabled - role active status
         * @param {Object} imageData - image data object
         * @private
         */
        _changeRole: function (imageType, isEnabled, imageData) {
            var needCheked = true;

            if (!isEnabled) {
                needCheked = this._gallery.find('input[name="product[' + imageType + ']"]').val() === imageData.file;
            }

            if (!needCheked) {
                return null;
            }

            this._gallery.trigger('setImageType', {
                type: imageType,
                imageData: isEnabled ? imageData : null
            });
        },

        /**
         * On open dialog
         * @param {Object} e
         * @param {Object} imageData
         * @private
         */
        _onOpenDialog: function (e, imageData) {
            var formFields, flagChecked, file,
                modal = this.element.closest('.mage-new-imgix-dialog');

            if (imageData['media_type'] === 'external-video') {
                this.imageData = imageData;
                modal.find('.image-upload-button').hide();
                modal.find('.video-delete-button').show();
                modal.find('.video-edit').show();
                modal.createVideoPlayer({
                    reset: true
                }).createVideoPlayer('reset');

                formFields = modal.find(this._videoFormSelector).find('.edited-data');

                $.each(formFields, function (i, field) {
                    $(field).val(imageData[field.name]);
                });

                flagChecked = imageData.disabled > 0;
                modal.find(this._videoDisableinputSelector).prop('checked', flagChecked);

                file = modal.find('#file_name').val(imageData.file);

                $.each(modal.find('.video_image_role'), function () {
                    $(this).prop('checked', false).prop('disabled', false);
                });

                $.each(this._gallery.find('.image-placeholder').siblings('input:hidden'), function () {
                    var start, end, imageRole;

                    if ($(this).val() === file.val()) {
                        start = this.name.indexOf('[') + 1;
                        end = this.name.length - 1;
                        imageRole = this.name.substring(start, end);
                        modal.find('#new_video_form input[value="' + imageRole + '"]').prop('checked', true);
                    }
                });
            }

        },

        /**
         * Toggle buttons
         */
        toggleButtons: function () {
            var self = this,
                modal = this.element.closest('.mage-new-imgix-dialog');

            modal.find('.video-placeholder, .add-imgix-button-container > button').click(function () {
                modal.find('.image-upload-button').show();
                modal.find('.video-delete-button').hide();
                modal.find('.video-edit').hide();
                modal.createVideoPlayer({
                    reset: true
                }).createVideoPlayer('reset').updateInputFields({
                    reset: true
                }).updateInputFields('reset');
            });
            this._gallery.on('click', '.item.video-item', function () {
                modal.find('.image-upload-button').hide();
                modal.find('.video-delete-button').show();
                modal.find('.video-edit').show();
                modal.find('.mage-new-imgix-dialog').createVideoPlayer({
                    reset: true
                }).createVideoPlayer('reset');
            });
            this._gallery.on('click', '.item.video-item:not(.removed)', function () {
                var flagChecked,
                    file,
                    formFields = modal.find('.edited-data'),
                    container = $(this);

                $.each(formFields, function (i, field) {
                    $(field).val(container.find('input[name*="' + field.name + '"]').val());
                });

                flagChecked = container.find('input[name*="disabled"]').val() > 0;
                self._gallery.find(self._videoDisableinputSelector).attr('checked', flagChecked);

                file = self._gallery.find('#file_name').val(container.find('input[name*="file"]').val());

                $.each(self._gallery.find('.video_image_role'), function () {
                    $(this).prop('checked', false).prop('disabled', false);
                });

                $.each(self._gallery.find('.image-placeholder').siblings('input:hidden'), function () {
                    var start, end, imageRole;

                    if ($(this).val() !== file.val()) {
                        return null;
                    }

                    start = this.name.indexOf('[') + 1;
                    end = this.name.length - 1;
                    imageRole = this.name.substring(start, end);
                    self._gallery.find('input[value="' + imageRole + '"]').prop('checked', true);
                });
            });
        }
    });

    return $.mage.newImgixDialog;
});
