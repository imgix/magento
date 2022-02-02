/*eslint-disable */
/* jscs:disable */
define(["Magento_PageBuilder/js/config", "Imgix_Magento/js/utils/image", "Magento_PageBuilder/js/utils/object", "Magento_PageBuilder/js/utils/url"], function (_config, _image, _object, _url) {

  /**
   * @api
   */
  var Src = /*#__PURE__*/function () {
    "use strict";

    function Src() {}

    var _proto = Src.prototype;

    /**
     * Convert value to internal format
     *
     * @param value string
     * @returns {string | object}
     */
    _proto.fromDom = function fromDom(value) {
      if (!value) {
        return "";
      }
      return (0, _image.decodeUrl)(value);
    }
    /**
     * Convert value to knockout format
     *
     * @param {string} name
     * @param {DataObject} data
     * @returns {string}
     */
    ;

    _proto.toDom = function toDom(name, data) {
      var value = (0, _object.get)(data, name);
      
      if (value[0] === undefined || value[0].url === undefined) {
        return "";
      }
      var imageUrl = value[0].url;

      // Check image url width and height exist, if exist width and height remove from imgix image url
      if( imageUrl.indexOf('?') != -1 ){
        imageUrl = imageUrl.split('?', 1)[0];
      }
      if( imageUrl.indexOf('&') != -1 ){
        imageUrl = imageUrl.split('&', 1)[0];
      }

      var params = [];
      // Append width to imgix url
      if (data.imgix_width !='' ){
        params.push("w="+data.imgix_width);
      } 
      // Append height to imgix url
      if (data.imgix_height !=''){
        params.push("h="+data.imgix_height);
      } 
      // Append format to imgix url
      if (data.imgix_format !=''){
        params.push("fm="+data.imgix_format);
      }
      // Append auto to imgix url
      if (data.imgix_auto !=''){
        params.push("auto="+data.imgix_auto);
      }
      // Append crop to imgix url
      if (data.imgix_crop !=''){
        params.push("crop="+data.imgix_crop);
        params.push("fit=crop");
      }
      if(params.length){
        imageUrl = imageUrl+"?"+params.join(("&"));
      } 
      return imageUrl;
    };

    return Src;
  }();

  return Src;
});
//# sourceMappingURL=src.js.map