var config = {
    config: {
        mixins: {
            'Magento_PageBuilder/js/form/element/image-uploader' : {
                'Imgix_Magento/js/form/element/image-uploader' : true
            }
        }
    },
    map: {
        '*': {
            newImgixDialog:  'Imgix_Magento/js/new-imgix-dialog',
            openImgixModal:  'Imgix_Magento/js/imgix-modal',
            productGallery:  'Imgix_Magento/js/product-gallery',
            'mage/adminhtml/browser':  'Imgix_Magento/js/browser',
            'Imgix_Magento/js/utils/image':  'Imgix_Magento/js/utils/image'
        }
    },
    paths: {
        ImgixClient: 'Imgix_Magento/node_modules/@imgix/js-core/dist/imgix-js-core.umd'
    }
};
