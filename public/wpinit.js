/** @namespace */
var dwv = dwv || {};

// special wp initialisation
// 'wp.pluginsUrl' is set by wordpress
dwv.wp = dwv.wp || {};
dwv.wp.init_was_called = false;
dwv.wp.init = function () {
  // avoid multiple calls
  if ( dwv.wp.init_was_called ) {
    return;
  }
  dwv.wp.init_was_called = true;

  var dwvPath = wp.pluginsUrl + '/dicom-support/node_modules/dwv';

  // image decoders (for web workers)
  dwv.decoderScripts = {
    jpeg2000: dwvPath + '/decoders/pdfjs/decode-jpeg2000.js',
    'jpeg-lossless': dwvPath + '/decoders/rii-mango/decode-jpegloss.js',
    'jpeg-baseline': dwvPath + '/decoders/pdfjs/decode-jpegbaseline.js',
    rle: dwvPath + '/decoders/dwv/decode-rle.js',
  };
};
