var config = {
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Imgix_Magento/js/swatch-renderer-mixin': true
            }
        }
    },
    map: {
        '*': {
            'fotorama/fotorama':  'Imgix_Magento/js/fotorama/fotorama'
        }
    },
    paths: {
        ImgixClient: 'Imgix_Magento/node_modules/@imgix/js-core/dist/imgix-js-core.umd'
    }
};
