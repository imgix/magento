/**
 * @api
 */
 define([
    'jquery',
    'underscore',
    'mage/template',
    'uiRegistry',
    'jquery/ui',
    'baseImage',
    'Magento_Catalog/js/product-gallery'
], function ($, _, mageTemplate, registry) {
    'use strict';

    /**
     * Product gallery widget
     */
    $.widget('imgix.productGallery', $.mage.productGallery, {
        options: {
            
        },

        /**
         * Gallery creation
         * @protected
         */
        _create: function () { 
            this.options.types = this.options.types || this.element.data('types');
            this.options.images = this.options.images || this.element.data('images');
            this.options.parentComponent = this.options.parentComponent || this.element.data('parent-component');

            this.imgTmpl = mageTemplate(this.element.find(this.options.template).html().trim());

            this._bind();
                      
            this.options.initialized = true;

            var template = this.element.find(this.options.dialogTemplate),
            containerTmpl = this.element.find(this.options.dialogContainerTmpl);

            this.modalPopupInit = false;

            if (template.length) {
                this.dialogTmpl = mageTemplate(template.html().trim());
            }

            if (containerTmpl.length) {
                this.dialogContainerTmpl = mageTemplate(containerTmpl.html().trim());
            } else {
                this.dialogContainerTmpl = mageTemplate('');
            }

            this._initDialog();
        }
    });

    return $.imgix.productGallery;
});
