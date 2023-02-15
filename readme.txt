=== DICOM Support ===
Contributors: ivmartel
Tags: dicom, html5, javascript
Requires at least: 4.5
Stable tag: 0.8.2
Tested up to: 6.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds DICOM (standard for medical image format) support to Wordpress!

== Description ==

The DICOM Support plugin adds the following functionalities to Wordpress:

* allows to upload DICOM (*.dcm) files in the media library,
* allows to insert DICOM individual files or galleries to a blog post from the media library (creates the short-code / gallery automatically).

The display is done using the open source DICOM Web Viewer ([DWV](https://github.com/ivmartel/dwv)).

See it live at: [dwvblog](https://tyarcaouen.synology.me/wordpress/dwvblog).

More details on how to add DICOM file(s) to your post can be found in the FAQ.

**WARNING**: for Wordpress 5 users, you cannot add dicom views with the new 'image' or 'gallery' blocks, you need to use a 'classic' block and then choose the ‘add media’ button.

== Installation ==

Upload the DICOM support plugin from the WordPress plugin directory to your blog and Activate it!

== Frequently Asked Questions ==

= DICOM? =
DICOM is the standard medical image format, see [DICOM](https://en.wikipedia.org/wiki/DICOM) on wikipedia or at [NEMA](http://dicom.nema.org/).

= Add an individual file =

The steps to add a DICOM file to a post are similar than adding an image except you do not have a preview in the editor.
They are:

1. In the post editor, click the `Add Media` button,
1. On the `Insert Media` page, choose the `Upload Files` tab,
1. Upload DICOM data,
1. It should be selected in the `Media Library` tab,
1. Click the `Insert into post` button,
1. This brings you back to the editor and adds the dcm shortcode to the post,
1. Click the `Preview Changes` button to see it in action!

= Add a gallery =

The gallery allows to load more than one slice and activates the scroll button.
The steps to add DICOM files to a gallery are similar than for images. They are:

1. In the post editor, click the `Add Media` button,
1. On the `Create Gallery` page, choose the `Upload Files` tab,
1. Upload DICOM data,
1. It should be selected in the `Media Library` tab,
1. **Note**: DICOM data may not show in the media list, in that case, choose the DICOM option in the first drop down on the search line,
1. Click the `Create a new gallery` button,
1. No need to change the DICOM files order in the `Edit Gallery` page, it will be set by the viewer; what you can do is choose the size in the `Settings` column,
1. Click the `Insert gallery` button,
1. This brings you back to the editor and adds the gallery shortcode to the post,
1. Click the `Preview Changes` button to see it in action!

Warning: this works with wordpress version inferior to 6...

= Error loading data =
DWV supports most of the DICOM standard but it can sometimes fail to load data. Please refer to the
[Dicom-Support](https://github.com/ivmartel/dwv/wiki/Dicom-Support) page on its wiki to see what it supports.
You can test your data on the latest live version of DWV on its [demo](http://ivmartel.github.io/dwv/demo/stable/viewers/mobile/index.html) page.

= Wordpress 5 blocks =
Wordpress 5 introduced a new [block editor](https://developer.wordpress.org/block-editor/). This plugin is not yet compatible with the 'image' or 'gallery' blocks, you need to use a 'classic' block and then choose the ‘add media’ button to insert DICOM data in your posts.

== Changelog ==

= 0.8.2 =
* Updated dwv to v0.26.2:
 * fix scroll

= 0.8.1 =
* Updated dwv to v0.26.1:
 * slow down scroll on trackpad
 * add shift to scroll shortcuts

= 0.8.0 =
* Updated dwv to v0.26.0:
 * support for RLE compressed files
 * support for non standard files

= 0.7.2 =
* Sync select with the current tool.

= 0.7.1 =
* Revert tool order to the one of the previous (v0.6) version.

= 0.7.0 =
* Updated dwv to v0.25.2:
 * support for DICOMDIR files (*.dcmdir)
 * no more modernizr dependency
* Real full screen (not opening the data in the simplistic viewer)
* Allow for setting a special window/level preset in shortcode or gallery

= 0.6.1 =
* Updated dwv to v0.22.1: fix MONOCHROME1 display
* Use a modernizr.js that include the 'mq' tests to allow
  compatibility with other theme/plugin

 = 0.6.0 =
* Updated dwv to v0.22.0:
 * more locales
 * updated decoders
 * improved DICOM parsing

= 0.5.2 =
* Updated dwv to v0.18.0:
  * better slice sorting,
  * allow for per slice window/level.

= 0.5.1 =
* Better js script insertion using wp_register_script and wp_add_inline_script.

= 0.5.0 =
* Updated dwv to v0.17.0:
  * fix Internet Explorer support,
  * added double click slice/frame play.
* Added Full screen link (and supporting pages).

= 0.4.1 =
* Add support for personnalised wordpress installation folder.
* Avoid race condition between listeners.

= 0.4 =
* Updated dwv to v0.16.0:
  * better DICOM parsing,
  * support for non encoded multi-frame data,
  * internationalsation.

= 0.3 =
* Added data decoders to support JPEG, JPEG LossLess and JPEG 2000.
* Updated doc.

= 0.2 =
* Added default values for width/height in dcm shortcode.
* Using uniqid instead of a hash of the input files.

= 0.1.1 =
Updated readme file.

= 0.1.0 =
Initial version.

== Upgrade Notice ==
No upgrades yet...
