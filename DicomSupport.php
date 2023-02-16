<?php
/*
Plugin Name: DICOM Support
Plugin URI:
Description: DICOM support for Wordpress: allows to upload DICOM (*.dcm) files in the media library and add them to a post. The display is done using the DICOM Web Viewer (<a href="https://github.com/ivmartel/dwv">DWV</a>).
Version: 0.8.2
Author: ivmartel
Author URI: https://github.com/ivmartel
*/

// publish steps: http://plugin.michael-simpson.com/?page_id=45

if (!class_exists("DicomSupport")) {

// DicomSupport class.
class DicomSupport {

  /**
  * Constructor.
  */
  function __construct() {
    load_plugin_textdomain('dcmobj');

    // add DICOM mime type to allowed upload
    add_filter('upload_mimes', array($this, 'upload_mimes'));

    // add 'dcm' DICOM view shortcode
    add_shortcode('dcm', array($this, 'dcm_shortcode'));
    // enqueue scripts on front end
    add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));

    // use 'dcm' shortcode when adding media to blog post
    add_filter('media_send_to_editor', array($this, 'media_send_to_editor'), 10, 3);
    // modify the output of the gallery short-code
    add_filter('post_gallery', array($this, 'post_gallery'), 10, 3);

    // allow 'dcm' in gallery
    add_action('admin_print_footer_scripts', array($this, 'admin_print_footer_scripts'));

    // add gallery options
    add_action('print_media_templates', array($this, 'print_media_templates'));
  }

  /**
  * Add DICOM (*.dcm) as a supported MIME type.
  * @see https://developer.wordpress.org/reference/hooks/upload_mimes/
  * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/upload_mimes
  * @param mime_types List of existing MIME types.
  */
  function upload_mimes($mime_types) {
    // add dcm to the list of mime types
    $mime_types['dcm'] = 'application/dicom';
    $mime_types['dcmdir'] = 'application/dicom';
    // return list
    return $mime_types;
  }

  /**
  * Create the DWV html.
  * @param urls The string of the urls to load.
  * @param width The width of the display.
  * @param height The height of the display.
  * @param windowCenter The window level center.
  * @param windowWidth The window level width.
  * @param wlName The name of the window level preset.
  */
  function create_dwv_html($urls,
    $width = 0,
    $height = 0,
    $windowCenter = 0,
    $windowWidth = 0,
    $wlName = 0) {
    // enqueue base scripts
    wp_enqueue_script('dwv-wordpress');
    wp_enqueue_style('dwv-wordpress');
    wp_enqueue_script('wpinit');

    // html var names
    $id = uniqid();
    $containerDivId = "dwv-" . $id;

    $style = '';
    if ( !empty($width) && $width != 0 &&
      !empty($height) && $height != 0 ) {
      $style .= 'width: '.$width.'px;';
      $style .= 'height: '.$height.'px;';
    }

    $wlSetting = "";
    if ( !empty($windowCenter) && $windowCenter != 0 &&
      !empty($windowWidth) && $windowWidth != 0 ) {
      $wlSetting = '// set post window level
        dwvApp.getViewController().addWindowLevelPresets({ "'.$wlName.'": {
          "wl": new dwv.image.WindowLevel('.$windowCenter.', '.$windowWidth.'),
          "name": "'.$wlName.'"} });
        dwvApp.getViewController().setWindowLevelPreset("'.$wlName.'");
        var index = dwvApp'.$id.'Gui.setSelectedPreset("'.$wlName.'");
        dwvApp'.$id.'Gui.SetDefaultSelectedIndex(index);';
    }
    // create app script
    // dwv.wp.init and listener flags were added in wp_enqueue_scripts defined below
    $script = '
    // namespace
    var dwvsimple = dwvsimple || {};
    // main application gui (global to be accessed from html)
    var dwvApp'.$id.'Gui = null;
    // start app function
    function startApp'.$id.'() {
      // initialise the application
      var dwvApp = new dwv.App();
      dwvApp.init({
        "containerDivId": "'.$containerDivId.'",
        "tools": ["Scroll", "ZoomAndPan", "WindowLevel"],
        "isMobile": true
      });
      // app gui
      dwvApp'.$id.'Gui = new dwvsimple.Gui(dwvApp);
      // listen to load-end
      dwvApp.addEventListener("load-end", function (/*event*/) {
        // enable actions
        dwvApp.getElement("tools").disabled = false;
        dwvApp.getElement("reset").disabled = false;
        dwvApp.getElement("presets").disabled = false;
        // if mono slice, remove scroll tool (default first)
        if (dwvApp.isMonoSliceData() && dwvApp.getImage().getNumberOfFrames() === 1) {
          var toolsSelect = dwvApp.getElement("tools");
          for (var i = 0; i < toolsSelect.options.length; ++i) {
            if (toolsSelect.options[i].value === "Scroll") {
              toolsSelect.remove(i);
            }
          }
        }
        // update presets
        dwvApp'.$id.'Gui.updatePresets(dwvApp.getViewController().getWindowLevelPresetsNames());
        '.$wlSetting.'
      });
      // listen to wl-center-change
      dwvApp.addEventListener("wl-center-change", function (/*event*/) {
        // update presets (in case new was added)
        dwvApp'.$id.'Gui.updatePresets(dwvApp.getViewController().getWindowLevelPresetsNames());
        // suppose it is a manual change so switch preset to manual
        dwvApp'.$id.'Gui.setSelectedPreset("manual");
      });
      // handle full screen exit
      function onFullscreenExit() {
        var container = document.getElementById("'.$containerDivId.'");
        var divs = container.getElementsByClassName("layerContainer");
        divs[0].setAttribute("style","'.$style.'");
        // resize app
        dwvApp.onResize();
      }
      handleFullscreenExit(onFullscreenExit);
      // load data
      dwvApp.loadURLs(['.$urls.']);
    }
    // launch when page and i18n are loaded
    function launchApp'.$id.'() {
      if ( domContentLoaded && i18nInitialised ) {
        startApp'.$id.'();
      }
    }
    dwv.i18nOnInitialised( function () {
      i18nInitialised = true;
      launchApp'.$id.'();
    });
    document.addEventListener("DOMContentLoaded", function (/*event*/) {
      domContentLoaded = true;
      launchApp'.$id.'();
    });';

    // add script to queue
    wp_add_inline_script('wpinit', $script);

    // create html
    $html = '
    <!-- Main container div -->
    <div id="'.$containerDivId.'" class="dwv">
      <!-- Toolbar -->
      <div class="toolbar">
        <label for="tools">TOOLS:</label>
        <select class="tools" name="tools" onChange="dwvApp'.$id.'Gui.onChangeTool(this.value)" disabled>
          <option value="Scroll" data-i18n="tool.Scroll.name">Scroll</option>
          <option value="ZoomAndPan" data-i18n="tool.ZoomAndPan.name">ZoomAndPan</option>
          <option value="WindowLevel" data-i18n="tool.WindowLevel.name">WindowLevel</option>
        </select>
        <label for="presets">PRESETS:</label>
        <select class="presets" name="presets" onChange="dwvApp'.$id.'Gui.onChangePreset(this.value)" disabled>
          <option value="">Preset...</option>
        </select>
        <button class="reset" value="Reset" onClick="dwvApp'.$id.'Gui.onDisplayReset()" data-i18n="basics.reset" disabled >Reset</button>
      </div>
      <!-- Layer Container -->
      <div class="layerContainer" style="'.$style.'">
        <canvas class="imageLayer">Only for HTML5 compatible browsers...</canvas>
      </div><!-- /layerContainer -->
    </div><!-- /dwv -->
    ';

    // 'full screen' link (out of dwv div to not be in full screen)
    $html .= '<p class="dwv-legend"><small><a href="#" onclick="makeFullscreen(\''.$containerDivId.'\');return false;">Full screen</a></small></p>';

    return $html;
  }

  /**
  * Interpret the 'dcm' shortcode to insert DICOM data in posts.
  * @see http://codex.wordpress.org/Shortcode_API
  * @param atts An associative array of attributes.
  * @param content The enclosed content.
  */
  function dcm_shortcode($atts, $content = null) {
    // check that we have a src attribute
    if ( empty($atts['src']) ) {
      return;
    }
    // width/height
    $width = 0;
    if ( !empty($atts['width']) ) {
      $width = $atts['width'];
    }
    $height = 0;
    if ( !empty($atts['height']) ) {
      $height = $atts['height'];
    }

    // window level
    $wc = 0;
    $ww = 0;
    if ( !empty($atts['window_center']) && !empty($atts['window_width']) ) {
      $wc = $atts['window_center'];
      $ww = $atts['window_width'];
    }
    $wlName = "Extra";
    if ( !empty($atts['wl_name']) ) {
      $wlName = $atts['wl_name'];
    }

    // split file list: given as "file1, file2",
    //   it needs to be passed as "file1", "file2"
    $fileList = array_map('trim', explode(',', $atts['src']));
    $urls = '"' . implode('","', $fileList) . '"';
    // return html
    return $this->create_dwv_html($urls, $width, $height, $wc, $ww, $wlName);
  }

  /**
  * Enqueue scripts for the front end.
  * @see https://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
  */
  function wp_enqueue_scripts() {
    $nodeModulesDir = 'node_modules';
    // i18n
    wp_register_script( 'i18next',
      plugins_url($nodeModulesDir . '/i18next/i18next.min.js', __FILE__ ),
      null, null );
    wp_register_script( 'i18next-xhr',
      plugins_url($nodeModulesDir . '/i18next-xhr-backend/i18nextXHRBackend.min.js', __FILE__ ),
      array('i18next'), null );
    wp_register_script( 'i18next-langdetect',
      plugins_url($nodeModulesDir . '/i18next-browser-languagedetector/i18nextBrowserLanguageDetector.min.js', __FILE__ ),
      array('i18next'), null );
    // jszip
    wp_register_script( 'jszip',
      plugins_url($nodeModulesDir . '/jszip/dist/jszip.min.js', __FILE__ ),
      null, null );
    // data decoders
    wp_register_script( 'dwv-rle',
      plugins_url($nodeModulesDir . '/dwv/decoders/dwv/rle.js', __FILE__ ),
      null, null );
    wp_register_script( 'pdfjs-ad',
      plugins_url($nodeModulesDir . '/dwv/decoders/pdfjs/arithmetic_decoder.js', __FILE__ ),
      null, null );
    wp_register_script( 'pdfjs-util',
      plugins_url($nodeModulesDir . '/dwv/decoders/pdfjs/util.js', __FILE__ ),
      null, null );
    wp_register_script( 'pdfjs-jpg',
      plugins_url($nodeModulesDir . '/dwv/decoders/pdfjs/jpg.js', __FILE__ ),
      array('pdfjs-ad', 'pdfjs-util'), null );
    wp_register_script( 'pdfjs-jpx',
      plugins_url($nodeModulesDir . '/dwv/decoders/pdfjs/jpx.js', __FILE__ ),
      array('pdfjs-ad', 'pdfjs-util'), null );
    wp_register_script( 'rii-loss',
      plugins_url($nodeModulesDir . '/dwv/decoders/rii-mango/lossless-min.js', __FILE__ ),
      null, null );
    // DWV base
    wp_register_script( 'dwv',
      plugins_url($nodeModulesDir . '/dwv/dist/dwv.min.js', __FILE__ ),
      array('i18next-xhr', 'i18next-langdetect', 'jszip', 'pdfjs-jpg', 'pdfjs-jpx', 'rii-loss', 'dwv-rle'), null );
    // wordpress viewer
    wp_register_script( 'dwv-wordpress',
      plugins_url('public/appgui.js', __FILE__ ),
      array( 'dwv' ), null );
    wp_register_style( 'dwv-wordpress',
      plugins_url('public/style.css', __FILE__ ) );

    // wp special
    wp_register_script( 'wpinit',
      plugins_url('public/wpinit.js', __FILE__ ),
      array( 'dwv' ), null );
    wp_localize_script( 'wpinit', 'wp', array('pluginsUrl' => plugins_url()) );
    $script = '
    // call special wp init
    dwv.wp.init();
    // listener flags
    var domContentLoaded = false;
    var i18nInitialised = false;';
    wp_add_inline_script('wpinit', $script);
  }

  /**
  * Insert shortcode when adding media to a blog post.
  * @see https://developer.wordpress.org/reference/hooks/media_send_to_editor/
  * @param html The default generated html.
  * @param id The id of the post.
  * @param attachment The post attachment.
  */
  function media_send_to_editor($html, $id, $attachment) {
    $post = get_post( $id ); // returns a WP_Post object
    // only process DICOM objects
    if ( $post->post_mime_type == 'application/dicom' ) {
      if ( !empty( $attachment['url'] )) {
        $html = '[dcm src="'.$attachment['url'].'"] ';
      }
    }
    return $html;
  }

  /**
  * Override media manager javascript functions to
  *  allow to select DICOM files to create galleries.
  * @see http://shibashake.com/wordpress-theme/how-to-expand-the-wordpress-media-manager-interface
  */
  function admin_print_footer_scripts() { ?>
    <script type="text/javascript">
    var updateMediaSearch = function () {
      // check vars
      if (typeof wp === 'undefined') {
        console.warn('Can\'t update media search, wp is undefined.');
        return;
      }
      if (typeof wp.media === 'undefined') {
        console.warn('Can\'t update media search, wp.media is undefined.');
        return;
      }
      // Add custom post type filters
      l10n = wp.media.view.l10n = typeof _wpMediaViewsL10n === 'undefined' ? {} : _wpMediaViewsL10n;
      wp.media.view.AttachmentFilters.Uploaded.prototype.createFilters = function () {
        var type = this.model.get('type');
        var types = wp.media.view.settings.mimeTypes;
        var text;
        if ( types && type ) {
          text = types[ type ];
        }

        var filters = {
          all: {
            text: text || l10n.allMediaItems,
            props: {
              uploadedTo: null,
              orderby: 'date',
              order: 'DESC'
            },
            priority: 20
          },

          uploaded: {
            text: l10n.uploadedToThisPost,
            props: {
              uploadedTo: wp.media.view.settings.post.id,
              orderby: 'menuOrder',
              order: 'ASC'
            },
            priority: 30
          },

          dicom: {
            text: 'DICOM',
            props: {
              type: 'application/dicom',
              uploadedTo: wp.media.view.settings.post.id,
              orderby: 'date',
              order: 'DESC'
            },
            priority: 10
          }
        };
        // Add post types only for gallery
        if (this.options.controller._state.indexOf('gallery') !== -1) {
          delete(filters.all);
          filters.image = {
            text: 'Images',
            props: {
              type: 'image',
              uploadedTo: null,
              orderby: 'date',
              order: 'DESC'
            },
            priority: 10
          };
          _.each( wp.media.view.settings.postTypes || {}, function ( text, key ) {
            filters[ key ] = {
              text: text,
              props: {
                type: key,
                uploadedTo: null,
                orderby: 'date',
                order: 'DESC'
              }
            };
          });
        }
        this.filters = filters;
      } // End create filters

      // Adding our search results to the gallery
      wp.media.view.MediaFrame.Post.prototype.mainGalleryToolbar = function ( view ) {
        var controller = this;

        this.selectionStatusToolbar( view );

        view.set( 'gallery', {
          style: 'primary',
          text: l10n.createNewGallery,
          priority: 60,
          requires: { selection: true },

          click: function () {
            var selection = controller.state().get('selection'),
            edit = controller.state('gallery-edit');
            //models = selection.where({ type: 'image' });

            // Don't filter based on type
            edit.set( 'library', selection);
            /*edit.set( 'library', new wp.media.model.Selection( selection, {
              props:    selection.props.toJSON(),
              multiple: true
            }) );*/

            this.controller.setState('gallery-edit');
          }
        });
      };
    } // end if (wp)
    </script>
  <?php }

  /**
   * Custom gallery settings.
   * @see https://developer.wordpress.org/reference/hooks/print_media_templates/
   * @see https://wordpress.stackexchange.com/questions/182821/add-custom-fields-to-wp-native-gallery-settings
   */
  function print_media_templates() { ?>
    <script type="text/html" id="tmpl-custom-gallery-setting">
      <h3 style="z-index: -1;">___________________________________________________________________________________________</h3>
      <h3>Optional Settings</h3>
      <label class="setting">
        <span><?php _e('Preset name'); ?></span>
        <input type="text" value="" data-setting="wl_name" style="float:right;">
      </label>
      <label class="setting">
        <span><?php _e('Window center'); ?></span>
        <input type="number" value="" data-setting="window_center" style="float:right;" min="1">
      </label>
      <label class="setting">
        <span><?php _e('Window width'); ?></span>
        <input type="number" value="" data-setting="window_width" style="float:right;" min="1">
      </label>
    </script>
    <script>
      jQuery(document).ready(function ()
      {
        _.extend(wp.media.gallery.defaults, {
          wl_name: 'Extra',
          window_center: '0',
          window_width: '0'
        });

        wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
          template: function (view){
            return wp.media.template('gallery-settings')(view) +
              wp.media.template('custom-gallery-setting')(view);
          }
        });
      });
    </script>
  <?php }

  /**
  * Modify the output of the gallery short-code for DICOM files.
  * @see https://developer.wordpress.org/reference/hooks/post_gallery/
  * @see https://codex.wordpress.org/Plugin_API/Filter_Reference/post_gallery
  * @param output The current output.
  * @param atts The attributes from the gallery shortcode.
  * @param instance Unique numeric ID of this gallery shortcode instance.
  */
  function post_gallery($output, $atts, $instance) {
    // attributes
    $atts = shortcode_atts( array(
      'order' => 'ASC',
      'orderby' => 'menu_order ID',
      'include' => '',
      'size' => 'full',
      'window_center' => 0,
      'window_width' => 0,
      'wl_name' => 'Extra'
      ), $atts, 'gallery'
    );

    // size
    $width = 0;
    $height = 0;
    if ( $atts['size'] == "thumbnail" ) {
      $width = 100;
      $height = 100;
    }
    else if ( $atts['size'] == "medium" ) {
      $width = 250;
      $height = 250;
    }
    else if ( $atts['size'] == "large" ) {
      $width = 500;
      $height = 500;
    }

    // window level
    $wc = 0;
    $ww = 0;
    if ( !empty($atts['window_center']) && !empty($atts['window_width']) ) {
      $wc = $atts['window_center'];
      $ww = $atts['window_width'];
    }
    $wlName = $atts['wl_name'];

    // get attachements
    // $atts['ids'] have been copied to $atts['include'],
    // see wp_include/media.php: function gallery_shortcode
    $_attachments = get_posts( array(
      'include' => $atts['include'],
      'post_status' => 'inherit',
      'post_type' =>  'attachment',
      'post_mime_type' => 'application/dicom',
      'order' => $atts['order'],
      'orderby' => $atts['orderby'] )
    );
    // build url list as string
    $urls = '';
    foreach ( $_attachments as $att ) {
      if ( $urls != '' ) {
        $urls .= ',';
      }
      $urls .= '"' . $att->guid . '"';
    }
    // return html
    // an empty output leads to default behaviour which will
    // be the case for non DICOM attachements
    $html = '';
    if ( $urls != '' ) {
      $html = $this->create_dwv_html($urls, $width, $height, $wc, $ww, $wlName);
    }
    return $html;
  }

} // end DicomSupport class

// Instanciate to create hooks.
$dcmSuppInstance = new DicomSupport();

} // end if (!class_exists("DicomSupport")) {

?>
