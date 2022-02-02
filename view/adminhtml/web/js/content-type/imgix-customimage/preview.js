function _inheritsLoose(subClass, superClass) { subClass.prototype = Object.create(superClass.prototype); subClass.prototype.constructor = subClass; _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

define([
    "Magento_PageBuilder/js/events", 
    "Magento_PageBuilder/js/content-type-menu/hide-show-option", 
    "Magento_PageBuilder/js/uploader",
    'Magento_PageBuilder/js/content-type/preview',
    'ImgixClient',
    "jquery"
], function (
    _events, _hideShowOption, _uploader, _preview,ImgixClient,$
) {

  /**
   * @api
   */
  var Preview = /*#__PURE__*/function (_preview2) {
    "use strict";

    _inheritsLoose(Preview, _preview2);

    function Preview() {
      return _preview2.apply(this, arguments) || this;
    }

    var _proto = Preview.prototype;

    /**
     * Return an array of options
     *
     * @returns {OptionsInterface}
     */
    _proto.retrieveOptions = function retrieveOptions() {
      var options = _preview2.prototype.retrieveOptions.call(this);

      options.hideShow = new _hideShowOption({
        preview: this,
        icon: _hideShowOption.showIcon,
        title: _hideShowOption.showText,
        action: this.onOptionVisibilityToggle,
        classes: ["hide-show-content-type"],
        sort: 150
      });
      return options;
    }
    /**
     * Get registry callback reference to uploader UI component
     *
     * @returns {Uploader}
     */
    ;

    _proto.getUploader = function getUploader() {
      var initialImageValue = this.contentType.dataStore.get(this.config.additional_data.uploaderConfig.dataScope, "");
      return new _uploader("imageuploader_" + this.contentType.id, this.config.additional_data.uploaderConfig, this.contentType.id, this.contentType.dataStore, initialImageValue);
    }
    /**
     * Get viewport image data
     */
    ;

    _proto.getViewportImageData = function getViewportImageData() {

      var params = null; 
      var sourceDomain = null; 
      var domain = null; 
      var imageName = null; 

      // Get image src 
      var url = this.data.desktop_image.attributes._latestValue.src;
      var imgix_width = this.data.desktop_image.attributes._latestValue.imgix_width;
      var imgix_height = this.data.desktop_image.attributes._latestValue.imgix_height;
      var imgix_auto = this.data.desktop_image.attributes._latestValue.imgix_auto;
      var imgix_crop = this.data.desktop_image.attributes._latestValue.imgix_crop;
      var imgix_format = this.data.desktop_image.attributes._latestValue.imgix_format;
      
      if(url.length){
        // Check url has ? or not 
        if (url.match(/\?./)) {
          params = url.split('?');
          // Get soucedomain from url
          url = params[0];
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
          this.data.desktop_image.attributes._latestValue.srcset = srcset;
        } else {
          var srcset = client.buildSrcSet(imageName);
          this.data.desktop_image.attributes._latestValue.srcset = srcset;
        }
      }
      return this.data.desktop_image;
      }
    /**
     * @inheritDoc
     */
    ;

    _proto.bindEvents = function bindEvents() {
      var _this = this;

      _preview2.prototype.bindEvents.call(this);

      _events.on("imgix_customimage:mountAfter", function (args) {
        if (args.id === _this.contentType.id) {
          _this.isSnapshot.subscribe(function (value) {
            _this.changeUploaderControlsVisibility();
          });
          _this.changeUploaderControlsVisibility();
        }
      });

      _events.on(this.config.name + ":" + this.contentType.id + ":updateAfter", function () {
        var files = _this.contentType.dataStore.get(_this.config.additional_data.uploaderConfig.dataScope);
       
        var imageObject = files ? files[0] : {};

        _events.trigger("imgix_customimage:" + _this.contentType.id + ":assignAfter", imageObject);
      });
    }
    /**
     * Change uploader controls visibility
     */
    ;

    _proto.changeUploaderControlsVisibility = function changeUploaderControlsVisibility() {
      var _this2 = this;

      this.getUploader().getUiComponent()(function (uploader) {
        uploader.visibleControls = !_this2.isSnapshot();
      });
    };

    return Preview;
  }(_preview);

  return Preview;
});
