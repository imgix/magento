/*eslint-disable */
/* jscs:disable */
define([
    "Magento_PageBuilder/js/utils/object",
    "ImgixClient",
    "jquery"
], function (_object,ImgixClient,$) {
    /**
     * Copyright © Magento, Inc. All rights reserved.
     * See COPYING.txt for license details.
     */
    var EmptyMobileImage = /*#__PURE__*/function () {
      "use strict";
  
      function EmptyMobileImage() {}
  
      var _proto = EmptyMobileImage.prototype;
  
      /**
       * Process data after it's read and converted by element converters
       *
       * @param {ConverterDataInterface} data
       * @param {object} config
       * @returns {object}
       */
      _proto.fromDom = function fromDom(data, config) {
        var desktopImage = (0, _object.get)(data, config.desktop_image_variable);
        var mobileImage = (0, _object.get)(data, config.mobile_image_variable);
  
        if (mobileImage && desktopImage && mobileImage[0] !== undefined && desktopImage[0] !== undefined && mobileImage[0].url === desktopImage[0].url) {
          delete data[config.mobile_image_variable];
        }
        return data;
      }
      /**
       * Process data before it's converted by element converters
       *
       * @param {ConverterDataInterface} data
       * @param {object} config
       * @returns {object}
       */
      ;
  
      _proto.toDom = function toDom(data, config) {
        var mobileImage = (0, _object.get)(data, config.mobile_image_variable);
  
        if (mobileImage === undefined || mobileImage[0] === undefined) {
          (0, _object.set)(data, config.mobile_image_variable, (0, _object.get)(data, config.desktop_image_variable));
        }
        var params = null; 
        var sourceDomain = null; 
        var domain = null; 
        var imageName = null;
        
        var imgix_width = data.imgix_width;
        var imgix_height = data.imgix_height;
        var imgix_auto = data.imgix_auto;
        var imgix_crop = data.imgix_crop;
        var imgix_format = data.imgix_format;

        if(data.imgix_customimage){
          var url = data.imgix_customimage[0].url;
          if(url.length){
            // Check url has ? or not 
            if (url.match(/\?./)) {
              params = url.split('?');
              // Get soucedomain from url
              url = params[0] ;
            } 
            // Start create params json object
            params = {};
            if (imgix_width !==''){
              params['w'] = imgix_width;
            }
            if (imgix_height !==''){
              params['h'] = imgix_height;
            }
            if (imgix_auto !==''){
              params['auto'] = imgix_auto;
            }
            if (imgix_crop !==''){
              params['crop'] = imgix_crop;
              params['fit'] = 'crop';
            }
            if (imgix_format !==''){
              params['fm'] = imgix_format;
            }
            // End create params json object

            // Get source domain and image name from url
            if (url.indexOf('imgix.net') > -1)
            {
              // Get imgix subdomain from the url
              sourceDomain = url.substr(0,url.indexOf('imgix.net') + 9 );
              // Remove https from imgix subdomain
              domain = sourceDomain.slice(8);
              // Get image name from url
              imageName = url.substring(url.indexOf('imgix.net/') + 9);
            }
            const client = new ImgixClient({
              domain: domain,
              secureURLToken: '',
            });
            if(params){
              var srcset = client.buildSrcSet(imageName, params);
              data.srcset= srcset;
            } else {
              var srcset = client.buildSrcSet(imageName);
              data.srcset= srcset;
            }
          }
        }
        return data;
      };
  
      return EmptyMobileImage;
    }();
  
    return EmptyMobileImage;
  });
  //# sourceMappingURL=empty-mobile-image.js.map