<?php
/*
Plugin Name: DICOM Support
Plugin URI:
Description: DICOM support for Wordpress: allows to upload DICOM (*.dcm) files in the media library and add them to a post. The display is done using the DICOM Web Viewer (<a href="https://github.com/ivmartel/dwv">DWV</a>).
Version: 0.10.3
Author: ivmartel
Author URI: https://github.com/ivmartel
*/

// publish steps: http://plugin.michael-simpson.com/?page_id=45

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_dcm_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_dcm_block_init' );

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
    // enqueue base style
    wp_enqueue_style('dwv-wordpress');
    // enqueue base scripts
    wp_enqueue_script('dwv-appgui');
    wp_enqueue_script('dwv-applaunch');
    wp_enqueue_script('wpinit');

    // html var names
    $id = uniqid();

    // create app script
    $script = "// App ".$id."\n";
    $script .= "document.addEventListener('DOMContentLoaded', function (/*event*/) {\n";
    $script .= "  startApp('".$id."',\n";
    $script .= "    {\n";
    $script .= "      urls: [".$urls."]";
    // possible input preset
    if ( !empty($windowCenter) && $windowCenter != 0 &&
      !empty($windowWidth) && $windowWidth != 0 ) {
      $script .= ",\n";
      $script .= "      wlpreset: {width: ".$windowWidth.
        ", center: ".$windowCenter.
        ", name: '".$wlName."'}";
    }
    // end options object
    $script .= "\n    }\n";
    $script .= "  )\n";
    $script .= "})\n";

    // add script to 'wpinit'
    $this->wpinit($script);

    // possible input size
    $style = '';
    if (!empty($width) && $width != 0) {
      $style .= 'width: '.$width.'px;';
    }
    if (!empty($height) && $height != 0) {
      $style .= 'height: '.$height.'px;';
    }
    if (!empty($style)) {
      $style = "style=\"" . $style . "\"";
    }

    // create html
    $html = '
    <!-- Main container div -->
    <div id="dwv-'.$id.'" class="dwv" '.$style.'>
      <!-- Toolbar -->
      <div id="toolbar-'.$id.'" class="toolbar"></div>
      <!-- Layer Container -->
      <div id="layerGroup-'.$id.'" class="layerGroup">
      </div>
    </div><!-- /dwv -->
    ';

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
    if ( empty($atts['src']) && empty($atts['ids']) ) {
      error_log('Missing required src or ids in dcm shortcode.');
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

    // split input list: given as "file1, file2",
    //   it needs to be passed as "file1", "file2"
    if ( !empty($atts['ids']) ) {
      // get url from media id
      $idToUrl = function(string $item): string {
        return wp_get_attachment_url(trim($item));
      };
      $fileList = array_map($idToUrl, explode(',', $atts['ids']));
    } else if ( !empty($atts['src']) ) {
      $fileList = array_map('trim', explode(',', $atts['src']));
    }
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
    // konva
    wp_register_script( 'konva',
      plugins_url($nodeModulesDir . '/konva/konva.min.js', __FILE__ ),
      null, null );
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
      array('konva', 'jszip', 'pdfjs-jpg', 'pdfjs-jpx', 'rii-loss', 'dwv-rle'), null );
    // wordpress viewer
    wp_register_script( 'dwv-appgui',
      plugins_url('public/appgui.js', __FILE__ ),
      array( 'dwv' ), null );
    wp_register_script( 'dwv-applaunch',
      plugins_url('public/applauncher.js', __FILE__ ),
      array( 'dwv' ), null );
    wp_register_style( 'dwv-wordpress',
      plugins_url('public/style.css', __FILE__ ) );

    // wpinit
    $this->wpinit(NULL);
  }

  /**
   * Add a js script to the 'wpinit' one. Registers 'wpinit' if
   * not already done.
   * @param script The script to add.
   */
  function wpinit($script) {
    // register the 'wpinit' script if not done yet
    // (it is possible 'wp_enqueue_scripts' was not called yet
    //  as for example in block themes)
    if ( !wp_script_is( 'wpinit', 'registered' ) ) {
      wp_register_script( 'wpinit',
        plugins_url('public/wpinit.js', __FILE__ ),
        array( 'dwv' ), null );
      // get the plugin url (to pass it to i18n)
      wp_localize_script( 'wpinit', 'wp', array('pluginsUrl' => plugins_url()) );
      // script to launch the wpinit function
      $script0 = "// call special wp init\n";
      $script0 .= "dwv.wp.init();\n";
      wp_add_inline_script('wpinit', $script0);
    }

    // extra, image specific, script
    if (!is_null($script)) {
      wp_add_inline_script('wpinit', $script);
    }
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
