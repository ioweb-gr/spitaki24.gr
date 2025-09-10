<?php

namespace WP_Smart_Image_Resize\Filters;

use WP_Smart_Image_Resize\Exceptions\Invalid_Image_Meta_Exception;
use WP_Smart_Image_Resize\Image_Meta;
use WP_Smart_Image_Resize\Processable_Trait;

class Generated_Sizes extends Base_Filter
{
    use Processable_Trait;

    public function listen()
    {
        add_filter('intermediate_image_sizes_advanced', [$this, 'removeUnwantedSizes'], 999, 3);
    }

    /**
     * Remove unwanted image sizes from the list of sizes to be generated.
     *
     * This method filters the list of image sizes to be generated, keeping only
     * the sizes that are configured in the plugin settings and not excluded.
     *
     * @param array $sizes    An array of image sizes.
     * @param array $metadata An array of image metadata.
     * @param int|null $image_id The ID of the attachment, or null if not available.
     *
     * @return array The filtered array of image sizes.
     */
    public function removeUnwantedSizes($sizes, $metadata, $image_id = null)
    {
        // We need the image ID to determine whether the uploaded
        // image is part of the processable images.
        if (! $image_id) {
            return $sizes;
        }

        try {
            $image_meta = new Image_Meta($image_id, $metadata);

            $isProcessable = $this->isProcessable($image_id, $image_meta);
            wp_cache_add('processable_image_'.$image_id, ($isProcessable ? 'yes' : 'no'), 'wp_sir_cache');
            
            if ($isProcessable) {
                $settings = wp_sir_get_settings();
                $excluded = _wp_sir_get_excluded_sizes();
                if (empty($excluded)) {
                    $filtered = array_intersect_key($sizes, array_flip($settings['sizes']));
                } else {
                    $filtered = array_intersect_key($sizes, array_flip(array_intersect($settings['sizes'], $excluded)));
                }
                return $filtered;
            }
        } catch (Invalid_Image_Meta_Exception $e) {
            error_log($e->getMessage());
        }

        return $sizes;
    }
}
