=== Smart Image Resize Pro ===
Contributors: nlemsieh
Tags: uniform images,same image size,woocommerce image resize,different image size,product image resize, image crop, image cut-off, resize image, fix image crop,photo resize,image crop, resize image without cropping, image resize, resize thumbnails
Requires at least: 4.0
Tested up to: 6.8
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Requires PHP: 5.6
Stable tag: 1.14.0

Make WooCommerce products images the same size and uniform without cropping.

== Description ==

Make WooCommerce products images the same size and uniform without cropping.

> Zero configuration.
> No more manual image editing and photo resizing.

### Features
- Resize images to any size.
- Remove unwanted whitespace from around image.
- Set a custom background color of the emerging area.
- Compress images to reduce file size.
- Select which sizes to generate.
- Convert to JPG format
- Use WebP Image

### Usage

SIR doesn't require any configuration. Just enable it under WooCommerce > Smart Image Resize, and you're ready to start uploading your images!

[Visit the guide](http://sirplugin.com/guide.html) to learn more.


 == Frequently Asked Questions ==

= How can I regenerate thumbnails I already added to the media library? =

[Visit the guide](https://sirplugin.com/guide.html#regenerating-thumbnails)

= I get an error when I upload an image =

Make sure PHP extension `fileinfo` is enabled.

== Screenshots ==

1. Before and after using the plugin.
2. Settings page.

== Changelog ==

= 1.14.0 =

* Various improvements and bugfixes. 

= 1.13.0 =

* Enhanced the "Bulk Regenerate Images" page for better user experience.
* Various improvements and bugfixes.

= 1.12.0 =

* Admin tweaks for better user experience.
* Minor bugfixes

= 1.11.0 =

* Added support for AVIF format.
* Admin tweaks for better user experience.
* Various improvements and bugfixes.

= 1.10.3 = 

* Introduced a new filter `wp_sir_exclude_trim_sizes` that allows excluding certain image sizes from the whitespace trimming functionality.
* Fixed a compatibility issue with the new version of the Phlox theme.

= 1.10.2 = 

* Various improvements and bugfixes.

= 1.10.0 = 

* Enhanced the settings page to improve user experience.
* Introduced a dedicated "Help" tab featuring setup guides and troubleshooting resources.
* Stability improvement.

= 1.9.5 = 

* Added an option to prevent upscaling of small images.

= 1.9.4 = 

* Improved compatibility with PHP 8.3
* Stability improvement

= 1.9.3 = 

* Stability improvement

= 1.9.2 = 

* Stability improvement

= 1.9.1 = 

* minor bugfixes

= 1.9.0 = 

* Addressed an issue with some thumbnail regeneration plugins not using the edited version of images modified in WordPress's built-in image editor

= 1.8.7 = 

* Added support for Phlox theme.

= 1.8.6 = 

* Declare compatibility with WooCommerce 9.3.

= 1.8.5 = 

* Declare compatibility with WordPress 6.6 and WooCommerce 9.1.

= 1.8.4 = 

* Fix an error encountered by certain users in v1.8.3 when processing images in the background.

= 1.8.3 = 

* Process image when `set_post_thumbnail` is called.
* Stability improvement

= 1.8.2 = 

* Stability improvement

= 1.8.1 = 

* Declare compatibility with custom order tables for WooCommerce.

= 1.8.0 = 

* Added a new experimental setting "Cropping mode". To enable it, add the filter: `add_filter('enable_experimental_features/crop_mode', '__return_true' );`

= 1.7.8 =

* Removed `is_feed` notice
* Declare compatibility with WooCommerce 7.0

= 1.7.7 =

* Improved compatibility with new themes and plugins
* Declare compatibility with WooCommerce 6.9
* Stability improvements

= 1.7.6.4 =

* Fixed an issue with the Trim whitespace's border size option not working properly in GD. 
* Stability improvements

= 1.7.6.3 =

* Minor bugfixes

= 1.7.6.2 =

* Fixed an issue in v1.7.6 causing some plugins' assets to not load properly.

= 1.7.6 =

* Deleted the option "Use WordPress cropping" as it seems to be causing some confusion for many users. To prevent specific sizes from being resized by the plugin use the filter `wp_sir_exclude_sizes` to return an array of size names you want to exclude.
* Fixed an issue with WebP files not deleted when the WebP feature is turned off.
* Declared compatibility with WooCommerce 6.3
* Added a work-around to fix a bug in Regenerate Thumbnails causing the latter to interfere with WPML.
* Stability improvements

= 1.7.5.4 =

* Declare compatibility with WooCommerce 6.1
* Declare compatibility with WP 5.9
* Stability improvements

= 1.7.5.3 =

* Fix a bug when background processing is trigged from the frontend.

= 1.7.5.2 =

* Bugfixes


= 1.7.5.1 =

*  Add fallback when used image processor fails to create WebP image. 

= 1.7.5 =

*  Recheck and process skipped images in the background after the parent post is saved.

= 1.7.4 =

* Replace "Resize fit mode" option with "Use WordPress cropping".
* Performance improvement.

= 1.7.3 =

* Fix blank WebP images with converting some PNG images in Imagick 6.x 
* Fix issue with Trimming border size limited to original image size.

= 1.7.2 =

* Improve CMYK images handling

= 1.7.1 =

* Format error message in WP CLI and avoid halting execution.
* Fix an issue with CMYK profile not being converted to RGB in Imagick.

= 1.7.0 =

* Add Watermark tool (beta).

= 1.6.4.2 =

* Use another image processor as fallback when current one doesn't support WebP.

= 1.6.4.1 =

* Fix WebP Images not served in Ajax responses

= 1.6.4 =

* Stability improvement
* Fix an issue with default image processor when Imagick doesn't support WebP. 

= 1.6.3 =

* Minor bugfixes 

= 1.6.2 =

* Add the ability to generate and serve WebP files for all images using the filter `wp_sir_generate_webp_for_all_images`.

= 1.6.1 =

* Add the ability to custom woocommerce default sizes.
* Stability improvement

= 1.6.0 =

* Add the ability to specify the resize fit mode for each size. 
* Stability improvement

= 1.5.5.1 =

* Stability improvement

= 1.5.5 =

* Fix color issue with some CMYK images.
* Fix faded images in some Imagick installs.

= 1.5.4 =

* Fix an issue with some themes not loading the correct image size.

= 1.5.3 =

* Stability improvement

= 1.5.2 =

* Fix thumbnail overwriten by WordPress when original image and thumbnail dimensions are identical
* Fix an issue with Flatsome using full size image instead of woocommerce_single for lazy load.
* Ignore sizes with 9999 dimension (unlimited height/width).
* Improve WebP availability detection.

= 1.5.1 =

* Use Imagick as default when available.
* Fix Avada not serving correct thumbnails on non-WooCommerce pages.
* Improve the user experience of the settings page. 


= 1.5.0 =

* Filter processed images in the media library toolbar
* Add filter `wp_sir_serve_webp_images`
* Improve Whitespace trimming tool  


= 1.4.10 =

* Declare compatibility with WooCommerce (v5.2)


= 1.4.9 =

* Use GD extension by default to process large images.


= 1.4.8 =

* Fixed an issue with some images in CMYK color.

= 1.4.7 =

* Fixed an issue with PNG-JPG conversion conflict
* Added support for WCFM plugin.
* Declared compatibility with WooCommerce (v5.0)
* Stability improvement


= 1.4.6.1 =

* Declared compatibility with WooCommerce (v4.9).

= 1.4.6 =

* Added tolerance level setting to trim away colors that differ slightly from pure white.
* Improved unwanted/old thumbnails clean up.

= 1.4.5 =

* Stability improvement.

= 1.4.4 =

* Improved bulk-resizing using Regenerate Thumbnails plugin.
* Stability improvement.

= 1.4.3.2 =

* Disabled WooCommerce thumbnails regeneration in the background to prevent reverting changes.

= 1.4.3.1 =

* Moved the license activation form to the plugin settings page under the "Manage License" tab.

= 1.4.3 =
* Fixed a minor issue with JPG images quality when compression is set to 0%.
* Stability improvement.

= 1.4.2.7 =
* Fixed an issue with UTF-8 encoded file names.

= 1.4.2.6 =

* Improved compatibility with WC product import tool.

= 1.4.2.5 =

* Fixed an issue when uploading non-image files occured in the previous update.

= 1.4.2.4 =

* Added abilitiy to activate multiple WP installations under the same domain.

= 1.4.2.3 =

* Turned off cache busting by default.

= 1.4.2.2 =

* Fixed WebP images not loading in some non-woocommerce pages.

= 1.4.2.1 =

* Fixed trimming issue for some image profiles (Imagick).
* Added an option to specify trimmed image border.

= 1.4.2 =

* Fixed an issue with WebP images used in Open Graph image (og:image)
* Improved resizing performances
* Stability improvement

= 1.4.1 =

* Fixed a bug with WebP not installed on server.
* Fixed an issue with front-end Media Library.

= 1.4.0 =

* Added support for category images.
* Ability to decide whether to resize an image being uploaded directly from the Media Library uploader.
* Support for WooCommerce Rest API
* Developers can use the boolean parameter `_processable_image` to upload requests to automatically process images.
* Added filter `wp_sir_maybe_upscale` to prevent small images upscale.
* Process image attachment with valid parent ID.
* Improved whitespace trimming by using Imagick.
* Fixed a tiny bug with compression only works for converted PNG-to-JPG images.
* Fixed an issue with srcset attribute caused non-adjusted images to load.
* Fixed an issue with trimmed images stretched when zoomed on the product page. 
* Improved support for bulk-import products.
* Improved processing performances with Imagick.

= 1.3.9 =

* Fix compatibility issue with Dokan vendor upload interface.
* Performances improvement.

= 1.3.8 =

 * Added compatibility with WP 5.4
 * Added support for WP Smush.
 * Added support for Dokan.
 * Stability improvement.

= 1.3.7 =

 * Stability improvement.

= 1.3.6 =

 * Fix a minor issue with image parent type detection.
 * Added a new filter `wp_sir_regeneratable_post_status` to change regeneratable product status. Default: `publish`

= 1.3.5 =

 * Regenerate thumbnails speed improvement.


= 1.3.4 =

 * Stability improvement

= 1.3.3 =

 * fixed a minor issue with settings page.

= 1.3.2 =
 * Added thumbnails regeneration steps under "Regenerate Thumbnails" tab.

= 1.3.1 =
 * Fixed a minor bug in Regenerate Thumbnails tool.

= 1.3 =
 * Added a built-in tool to regenerate thumbnails.
 * woocommerce_single size is now selected by default.
 * Stability improvement.

= 1.2.4 =
 * Fix srcset images not loaded when WebP is enabled.
 
= 1.2.3 =
 * Set GD driver as default.
 * Stability improvement.

= 1.2.2 =
 * Prevent black background when converting transparent PNG to JPG.
 * Fixed random issue that causes WebP images fail to load.
 * Disabled license notice.
 * Stability improvement.

= 1.2.1 =

* Added settings links
* Fix minor bug with WebP

= 1.2.0 =

* Added Whitespace Trimming feature.
* Various improvements. 

= 1.1.12 =

* Fixed crash when Fileinfo extension is disabled. 

= 1.1.11 =

* Added support for Jetpack. 

= 1.1.10 =

* Fixed conflict with some plugins. 

= 1.1.9 =

* Prevent dynamic resize in WooCommerce.

= 1.1.8 =

* Handle WebP not installed.

= 1.1.7 =

* Fixed mbstring polyfill conflict with WP `mb_strlen` function

= 1.1.6 =
* Added polyfill for PHP mbstring extension

= 1.1.5 =
* Force square image when height is set to auto.

= 1.1.4 =
* Fixed empty sizes list 

= 1.1.3 =
* Fixed empty sizes list 

= 1.1.2 =

* Added settings improvements
* Added processed images notice.

= 1.1.1 =

* Added fileinfo and PHP version notices
* Improved settings page experience.

= 1.1.0 =

Initial release of Smart Image Resize Pro

 == Upgrade Notice ==
 
  = 1.7.0 =

* Adding watermark is now available in beta.

