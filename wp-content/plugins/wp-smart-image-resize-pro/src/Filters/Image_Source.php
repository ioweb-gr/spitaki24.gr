<?php

namespace WP_Smart_Image_Resize\Filters;

use Exception;
use WP_Smart_Image_Resize\Utilities\Request;
use WP_Smart_Image_Resize\Utilities\Env;
use WP_Smart_Image_Resize\Image_Filters\CreateWebP_Filter;
use WP_Smart_Image_Resize\Image_Filters\Trim_Filter;
use WP_Smart_Image_Resize\Image_Manager;
use WP_Smart_Image_Resize\Image_Meta;

class Image_Source extends Base_Filter {
    public function listen() {

        global $wp_version;

        
        
        if (apply_filters('wp_sir_serve_webp_images', true)) {
            add_filter('wp_get_attachment_image_src', [$this, 'replaceSrcWebpExtension'], (PHP_INT_MAX - 1), 4);
            add_filter('wp_calculate_image_srcset_meta', [$this, 'replaceWebPExtensionSrcsetMetadata'], (PHP_INT_MAX - 1), 4);
            add_filter('wp_get_attachment_url', [$this, 'replaceWebPAttachmentUrlEextension'], (PHP_INT_MAX - 1), 2);
        }

        

        if (version_compare($wp_version, '4.9', '>=')) {
            add_filter('post_thumbnail_size', [$this, 'force_thumbnail_size'], 10, 2);
        }
        add_filter('wp_calculate_image_srcset', [$this, 'fix_srcset_subsize_overwritten_by_fullsize'], 10, 5);
        add_filter('wp_get_attachment_image_attributes', [$this, 'flatsome_use_thumbnail_in_lazyload']);
        add_filter('wp_calculate_image_srcset', [$this, 'remove_srcset_if_has_excluded_size'], PHP_INT_MAX, 5);
    }

    function remove_srcset_if_has_excluded_size($sources, $size_array, $image_src, $image_meta, $image_id) {

    if (!apply_filters('wp_sir_remove_srcset_mixed_fit_mode', true)) {
        return $sources;
    }

    if (!isset($image_meta['_processed_at'])) {
        return $sources;
    }

    $has_mixed  = wp_cache_get('wp_sir_srcset_mixed_fit_mode');

    if(! $has_mixed ){
        $has_mixed = !empty(_wp_sir_get_excluded_sizes()) ? 'yes' : 'no';
        wp_cache_set('wp_sir_srcset_mixed_fit_mode', $has_mixed);
    }

    return  $has_mixed === 'yes' ? false : $sources;

    }

    /**
     * This function will replace the data-src with the main image src.
     * 
     */
    function flatsome_use_thumbnail_in_lazyload($attr) {

        if (!apply_filters('wp_sir_flatsome_use_thumbnail_in_lazyload', true)) {
            return $attr;
        }

        // Only on single product pages.
        if (!function_exists('is_product') || !is_product()) {
            return $attr;
        }

        // Check if Flatsome is active.
        if (!function_exists('flatsome_wc_get_gallery_image_html')) {
            return $attr;
        }

        if ((!is_admin() && !is_customize_preview()) && get_theme_mod('lazy_load_images')) {
            $attr['data-src'] = $attr['src'];
        }

        return $attr;
    }

    function fix_srcset_subsize_overwritten_by_fullsize($sources, $size_array, $image_src, $image_meta, $image_id) {

        // Verify if the meta is valid.
        if (!is_array($image_meta) || empty($image_meta) || empty($image_meta['sizes'])) {
            return $sources;
        }

        //  Check if the image has been already processed.
        if (!isset($image_meta['_processed_at'])) {
            return $sources;
        }

        if (empty($size_array)) {
            return $sources;
        }

        list($image_w, $image_h) = $size_array;

        // Search for the missing size.
        $missing_size = false;

        foreach ($image_meta['sizes'] as $size_data) {
            if ($image_w === $size_data['width'] && $image_h === $size_data['height']) {
                $missing_size = $size_data;
                break;
            }
        }

        if (!$missing_size) {
            return $sources;
        }

        if (isset($sources[$image_w]) && isset($sources[$image_w]['url'])) {
            $sources[$image_w]['url'] = trailingslashit(dirname($sources[$image_w]['url'])) . $missing_size['file'];
        }

        return $sources;
    }

    /**
     * This is a workaround to force WordPress to load thumbnail instead of full size when
     * a third-party is using get_the_post_thumbnail().
     * 
     * @param string|int[] $size
     * @param int $post_id
     * 
     * @return string
     */
    public function force_thumbnail_size($size, $post_id) {
        // Let developers bypass this filter.
        if (!apply_filters('wp_sir_force_thumbnail_size', true)) {
            return $size;
        }

        // Only when the full size is passed.
        if (is_string($size) && $size !== 'full') {
            return $size;
        }

        // Only for product-images.
        // TODO: Support other processable post types.
        $post_type = get_post_type($post_id);

        if (empty($post_type) || strpos($post_type, 'product') === false) {
            return $size;
        }

        if (function_exists('is_product') && is_product()) {
            return 'woocommerce_single';
        }

        return  'woocommerce_thumbnail';
    }

    

    function replaceWebPAttachmentUrlEextension($url, $imageId) {

        if (!apply_filters('wp_sir_attachment_url_webp', false)) {
            return $url;
        }
        try {
            if (wp_attachment_is_image($imageId)) {
                list($url) = $this->replaceSrcWebpExtension([$url], $imageId, 'full', false);
                if (!empty($url)) {
                    return $url;
                }
            }
        } catch (Exception $e) {
        }

        return $url;
    }


    /**
     * Match src.
     *
     * @param array $metadata
     * @param array $imageArr
     * @param string $imageSrc
     * @param int $attachmentId
     *
     * @return array
     */

    public function replaceWebPExtensionSrcsetMetadata($metadata, $imageArr, $imageSrc, $attachmentId) {
        try {

            if (!$this->shouldServeWebP()) {
                return $metadata;
            }

            $imageMeta = new Image_Meta($attachmentId, $metadata);

            if (!$imageMeta->getMetaItem('_processed_at')) {
                return $metadata;
            }

            foreach ($imageMeta->getSizeNames() as $name) {
                $imageMeta->setSizeExtension($name, 'webp');
            }

            return $imageMeta->toArray();
        } catch (Exception $e) {
            return $metadata;
        }
    }

    /**
     * Use WebP if enabled and supported by the browser.
     * Fallback to standard format.
     *
     * @param array|false $image
     * @param int $attachmentId
     * @param string|array $size
     * @param bool $icon
     *
     * @return array|false
     */

    public function replaceSrcWebpExtension($image, $attachmentId, $size, $icon) {



        if ($icon || !is_array($image) || !$this->shouldServeWebP()) {
            return $image;
        }

        try {

            $imageMeta = new Image_Meta($attachmentId, wp_get_attachment_metadata($attachmentId));

            if (!$imageMeta->getMetaItem('_processed_at')) {
                return $image;
            }
            // Get the nearest size name to the given array.
            if (is_array($size)) {

                $intermediate = image_get_intermediate_size($attachmentId, $size);

                $size = $imageMeta->findSizeByFile($intermediate);

                if (empty($size)) {
                    return $image;
                }
            }

            if (!$imageMeta->hasSize($size)) {
                return $image;
            }

            $webpPath = $imageMeta->getSizeFullPath($size, 'webp');

            if (!file_exists($webpPath)) {
                return $image;
            }

            $sizeData = $imageMeta->getSizeData($size, true);

            $imageUrl = $imageMeta->getSizeUrl($size, 'webp');

            return [
                $imageUrl,
                $sizeData['width'],
                $sizeData['height'],
            ];
        } catch (Exception $e) {
        }

        return $image;
    }

    /**
     * Determine whether to load WebP images.
     *
     * @return bool
     */
    public function shouldServeWebP() {

        if (doing_action('wp_head')) {
            return false;
        }

        if (!wp_sir_get_settings()['enable_webp']) {
            return false;
        }

        if (!Env::get_webp_image_processor()) {
            return false;
        }

        if (!Request::is_front_end()) {
            return false;
        }

        return Env::browser_supposts_webp();
    }

    
}
