<?php
/**
 * Main functions for rendering gallery html and tweaks to PDP's for compatibility with other plugins
 *
 * @author   CommerceGurus
 * @package  CommerceGurus_Attributes_Gallery
 * @since    1.0.0
 */

/**
 * Get product attributes gallery admin tab.
 *
 * @param string $tabs admin product tabs.
 */
function commercegurus_get_attributes_gallery_tab( $tabs ) {
	$tabs['commercekit_gallery'] = array(
		'label'    => esc_html__( 'Attributes Gallery', 'commercegurus-commercekit' ),
		'target'   => 'cgkit_attr_gallery',
		'class'    => array( 'commercekit-attributes-gallery', 'show_if_variable' ),
		'priority' => 61,
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'commercegurus_get_attributes_gallery_tab' );

/**
 * Get product attributes gallery admin panel.
 */
function commercegurus_get_attributes_gallery_panel() {
	global $post;
	$product_id = $post->ID;
	$attributes = commercegurus_attributes_load_attributes( $product_id );

	$commercekit_image_gallery = get_post_meta( $product_id, 'commercekit_image_gallery', true );
	$commercekit_video_gallery = get_post_meta( $product_id, 'commercekit_video_gallery', true );
	require_once dirname( __FILE__ ) . '/templates/admin-product-attributes-gallery.php';
}
add_filter( 'woocommerce_product_data_panels', 'commercegurus_get_attributes_gallery_panel' );

/**
 * Add admin CSS and JS scripts
 */
function commercegurus_attributes_admin_scripts() {
	$screen = get_current_screen();
	if ( 'product' === $screen->post_type && 'post' === $screen->base ) {
		wp_enqueue_style( 'woocommerce-select2-styles', WC()->plugin_url() . '/assets/css/select2.css', array(), WC()->version );
		wp_enqueue_style( 'commercekit-attributes-admin-style', CKIT_URI . 'assets/css/admin-product-attributes-gallery.css', array(), CGKIT_CSS_JS_VER );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'commercekit-attributes-admin-script', CKIT_URI . 'assets/js/admin-product-attributes-gallery.js', array(), CGKIT_CSS_JS_VER, true );
	}
}
add_action( 'admin_enqueue_scripts', 'commercegurus_attributes_admin_scripts' );

/**
 * Save product attributes gallery
 *
 * @param string $post_id post ID.
 * @param string $post post.
 */
function commercegurus_save_product_attributes_gallery( $post_id, $post ) {
	if ( 'product' !== $post->post_type ) {
		return;
	}
	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( $commercekit_nonce && wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
		if ( $post_id ) {
			$image_gallery = isset( $_POST['commercekit_image_gallery'] ) ? map_deep( wp_unslash( $_POST['commercekit_image_gallery'] ), 'sanitize_textarea_field' ) : array();
			update_post_meta( $post_id, 'commercekit_image_gallery', $image_gallery );
			$video_gallery = isset( $_POST['commercekit_video_gallery'] ) ? map_deep( wp_unslash( $_POST['commercekit_video_gallery'] ), 'sanitize_textarea_field' ) : array();
			update_post_meta( $post_id, 'commercekit_video_gallery', $video_gallery );
		}
	}
}
add_action( 'woocommerce_process_product_meta', 'commercegurus_save_product_attributes_gallery', 10, 2 );

/**
 * Get ajax product gallery
 */
function commercegurus_get_ajax_product_gallery() {
	$ajax           = array();
	$ajax['status'] = 0;
	$ajax['html']   = '';

	$commercekit_nonce = isset( $_GET['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}

	$product_id = isset( $_GET['product_id'] ) ? (int) $_GET['product_id'] : 0; // phpcs:ignore
	if ( $product_id ) {
		ob_start();
		$attributes   = commercegurus_attributes_load_attributes( $product_id );
		$without_wrap = true;

		$commercekit_image_gallery = get_post_meta( $product_id, 'commercekit_image_gallery', true );
		$commercekit_video_gallery = get_post_meta( $product_id, 'commercekit_video_gallery', true );

		require_once dirname( __FILE__ ) . '/templates/admin-product-attributes-gallery.php';

		$ajax['status'] = 1;
		$ajax['html']   = ob_get_contents();
		ob_clean();
	}

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_get_ajax_product_gallery', 'commercegurus_get_ajax_product_gallery' );

/**
 * Update ajax product gallery
 */
function commercegurus_update_ajax_product_gallery() {
	$ajax           = array();
	$ajax['status'] = 0;
	$ajax['html']   = '';

	$commercekit_nonce = isset( $_POST['commercekit_nonce_ag'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce_ag'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}

	$product_id = isset( $_GET['product_id'] ) ? (int) $_GET['product_id'] : 0; // phpcs:ignore
	if ( $product_id ) {
		$post = get_post( $product_id );
		commercegurus_save_product_attributes_gallery( $product_id, $post );
		$ajax['status'] = 1;
	}

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_update_ajax_product_gallery', 'commercegurus_update_ajax_product_gallery' );

/**
 * The Elementor\Frontend class runs its register_scripts() method on
 * wp_enqueue_scripts at priority 5, so we want to hook in after this has taken place.
 */
add_action( 'wp_enqueue_scripts', 'commercegurus_attributes_elementor_frontend_scripts_modifier', 6 );

/**
 * Elementor frontend scripts modifier
 */
function commercegurus_attributes_elementor_frontend_scripts_modifier() {

	if ( ! is_product() ) {
		return;
	}

	// Get all scripts.
	$scripts = wp_scripts();

	// Bail if something went wrong.
	if ( ! ( $scripts instanceof WP_Scripts ) ) {
		return;
	}

	// Array of handles to remove.
	$handles_to_remove = array( 'swiper' );

	// Flag indicating if we have removed the handles.
	$handles_updated = false;

	// Remove desired handles from the elementor-frontend script.
	foreach ( $scripts->registered as $dependency_object_id => $dependency_object ) {

		if ( 'elementor-frontend' === $dependency_object_id ) {

			// Bail if something went wrong.
			if ( ! ( $dependency_object instanceof _WP_Dependency ) ) {
				return;
			}

			// Bail if there are no dependencies for some reason.
			if ( empty( $dependency_object->deps ) ) {
				return;
			}

			// Do the handle removal.
			foreach ( $dependency_object->deps as $dep_key => $handle ) {
				if ( in_array( $handle, $handles_to_remove, true ) ) {
					unset( $dependency_object->deps[ $dep_key ] );
					$dependency_object->deps = array_values( $dependency_object->deps );  // "reindex" array
					$handles_updated         = true;
				}
			}
		}
	}

	// If we have updated the handles, dequeue the relevant dependencies which
	// were enqueued separately Elementor\Frontend.
	if ( $handles_updated ) {
		wp_dequeue_script( 'swiper' );
		wp_deregister_script( 'swiper' );
	}
}

/**
 * Attributes gallary WPML make duplicate
 *
 * @param string $master_post_id  master post id.
 * @param string $target_lang     target language.
 * @param string $post_array      post array.
 * @param string $target_post_id  target post id.
 */
function commercegurus_attributes_gallary_wpml_make_duplicate( $master_post_id, $target_lang, $post_array, $target_post_id ) {
	if ( 'product' !== get_post_type( $master_post_id ) ) {
		return;
	}
	$product = wc_get_product( $master_post_id );
	if ( ! $product || ( method_exists( $product, 'is_type' ) && ! $product->is_type( 'variable' ) ) ) {
		return;
	}

	$attributes = commercegurus_attributes_load_attributes( $master_post_id );
	$term_taxes = array();
	if ( count( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			if ( isset( $attribute['taxo'] ) && 1 === (int) $attribute['taxo'] ) {
				if ( is_array( $attribute['terms'] ) && count( $attribute['terms'] ) ) {
					foreach ( $attribute['terms'] as $term_tax ) {
						$term_taxes[ $term_tax->term_id ] = $term_tax->taxonomy;
					}
				}
			}
		}
	}

	$images  = get_post_meta( $master_post_id, 'commercekit_image_gallery', true );
	$nimages = array();
	if ( is_array( $images ) && count( $images ) ) {
		foreach ( $images as $term_id => $imgs ) {
			$tmp_ids = explode( '_cgkit_', $term_id );
			$trm_ids = array();
			if ( count( $tmp_ids ) ) {
				foreach ( $tmp_ids as $tmp_id ) {
					if ( is_numeric( $tmp_id ) && array_key_exists( $tmp_id, $term_taxes ) ) {
						$trm_ids[] = apply_filters( 'wpml_object_id', $tmp_id, $term_taxes[ $tmp_id ], true, $target_lang );
					} else {
						$trm_ids[] = $tmp_id;
					}
				}
			}
			$term_id = implode( '_cgkit_', $trm_ids );

			$tmp_imgs = explode( ',', $imgs );
			$trn_imgs = array();
			if ( count( $tmp_imgs ) ) {
				foreach ( $tmp_imgs as $tmp_img ) {
					$trn_imgs[] = apply_filters( 'wpml_object_id', $tmp_img, 'attachment', true, $target_lang );
				}
			}
			$imgs = implode( ',', $trn_imgs );

			$nimages[ $term_id ] = $imgs;
		}
	}
	update_post_meta( $target_post_id, 'commercekit_image_gallery', $nimages );

	$videos  = get_post_meta( $master_post_id, 'commercekit_video_gallery', true );
	$nvideos = array();
	if ( is_array( $videos ) && count( $videos ) ) {
		foreach ( $videos as $term_id => $vids ) {
			$tmp_ids = explode( '_cgkit_', $term_id );
			$trm_ids = array();
			if ( count( $tmp_ids ) ) {
				foreach ( $tmp_ids as $tmp_id ) {
					if ( is_numeric( $tmp_id ) && array_key_exists( $tmp_id, $term_taxes ) ) {
						$trm_ids[] = apply_filters( 'wpml_object_id', $tmp_id, $term_taxes[ $tmp_id ], true, $target_lang );
					} else {
						$trm_ids[] = $tmp_id;
					}
				}
			}
			$term_id = implode( '_cgkit_', $trm_ids );

			$nvids = array();
			if ( is_array( $vids ) && count( $vids ) ) {
				foreach ( $vids as $tmp_img => $value ) {
					$tmp_img = apply_filters( 'wpml_object_id', $tmp_img, 'attachment', true, $target_lang );

					$nvids[ $tmp_img ] = $value;
				}
			}
			$nvideos[ $term_id ] = $nvids;
		}
	}
	update_post_meta( $target_post_id, 'commercekit_video_gallery', $nvideos );

	$wc_videos  = get_post_meta( $master_post_id, 'commercekit_wc_video_gallery', true );
	$wc_nvideos = array();
	if ( is_array( $wc_videos ) && count( $wc_videos ) ) {
		foreach ( $wc_videos as $tmp_img => $value ) {
			$tmp_img = apply_filters( 'wpml_object_id', $tmp_img, 'attachment', true, $target_lang );

			$wc_nvideos[ $tmp_img ] = $value;
		}
	}
	update_post_meta( $target_post_id, 'commercekit_wc_video_gallery', $wc_nvideos );
}
add_action( 'icl_make_duplicate', 'commercegurus_attributes_gallary_wpml_make_duplicate', 10, 4 );

/**
 * Attribute gallary Polylang Pro copy metas
 *
 * @param string $meta_array     array of metadata.
 * @param string $sync           sync or copy.
 * @param string $master_post_id master post ID.
 * @param string $target_post_id target post id.
 * @param string $target_lang    target language code.
 */
function commercegurus_attributes_gallary_pll_copy_post_metas( $meta_array, $sync, $master_post_id, $target_post_id, $target_lang ) {
	if ( 'product' !== get_post_type( $master_post_id ) ) {
		return $meta_array;
	}
	$product = wc_get_product( $master_post_id );
	if ( ! $product || ( method_exists( $product, 'is_type' ) && ! $product->is_type( 'variable' ) ) ) {
		return $meta_array;
	}

	$attributes = commercegurus_attributes_load_attributes( $master_post_id );
	$term_taxes = array();
	if ( count( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			if ( isset( $attribute['taxo'] ) && 1 === (int) $attribute['taxo'] ) {
				if ( is_array( $attribute['terms'] ) && count( $attribute['terms'] ) ) {
					foreach ( $attribute['terms'] as $term_tax ) {
						$term_taxes[ $term_tax->term_id ] = $term_tax->taxonomy;
					}
				}
			}
		}
	}

	$images  = get_post_meta( $master_post_id, 'commercekit_image_gallery', true );
	$nimages = array();
	if ( is_array( $images ) && count( $images ) ) {
		foreach ( $images as $term_id => $imgs ) {
			$tmp_ids = explode( '_cgkit_', $term_id );
			$trm_ids = array();
			if ( count( $tmp_ids ) ) {
				foreach ( $tmp_ids as $tmp_id ) {
					if ( is_numeric( $tmp_id ) && array_key_exists( $tmp_id, $term_taxes ) ) {
						$trm_ids[] = commercekit_pll_get_term( $tmp_id, $target_lang );
					} else {
						$trm_ids[] = $tmp_id;
					}
				}
			}
			$term_id = implode( '_cgkit_', $trm_ids );

			$tmp_imgs = explode( ',', $imgs );
			$trn_imgs = array();
			if ( count( $tmp_imgs ) ) {
				foreach ( $tmp_imgs as $tmp_img ) {
					$trn_imgs[] = commercekit_pll_get_post( $tmp_img, $target_lang );
				}
			}
			$imgs = implode( ',', $trn_imgs );

			$nimages[ $term_id ] = $imgs;
		}
	}
	update_post_meta( $target_post_id, 'commercekit_image_gallery', $nimages );

	$videos  = get_post_meta( $master_post_id, 'commercekit_video_gallery', true );
	$nvideos = array();
	if ( is_array( $videos ) && count( $videos ) ) {
		foreach ( $videos as $term_id => $vids ) {
			$tmp_ids = explode( '_cgkit_', $term_id );
			$trm_ids = array();
			if ( count( $tmp_ids ) ) {
				foreach ( $tmp_ids as $tmp_id ) {
					if ( is_numeric( $tmp_id ) && array_key_exists( $tmp_id, $term_taxes ) ) {
						$trm_ids[] = commercekit_pll_get_term( $tmp_id, $target_lang );
					} else {
						$trm_ids[] = $tmp_id;
					}
				}
			}
			$term_id = implode( '_cgkit_', $trm_ids );

			$nvids = array();
			if ( is_array( $vids ) && count( $vids ) ) {
				foreach ( $vids as $tmp_img => $value ) {
					$tmp_img = commercekit_pll_get_post( $tmp_img, $target_lang );

					$nvids[ $tmp_img ] = $value;
				}
			}
			$nvideos[ $term_id ] = $nvids;
		}
	}
	update_post_meta( $target_post_id, 'commercekit_video_gallery', $nvideos );

	$wc_videos  = get_post_meta( $master_post_id, 'commercekit_wc_video_gallery', true );
	$wc_nvideos = array();
	if ( is_array( $wc_videos ) && count( $wc_videos ) ) {
		foreach ( $wc_videos as $tmp_img => $value ) {
			$tmp_img = commercekit_pll_get_post( $tmp_img, $target_lang );

			$wc_nvideos[ $tmp_img ] = $value;
		}
	}
	update_post_meta( $target_post_id, 'commercekit_wc_video_gallery', $wc_nvideos );

	$meta_keys = array( 'commercekit_image_gallery', 'commercekit_video_gallery', 'commercekit_wc_video_gallery' );
	foreach ( $meta_array as $key => $meta_key ) {
		if ( in_array( $meta_key, $meta_keys, true ) ) {
			unset( $meta_array[ $key ] );
		}
	}

	return $meta_array;
}
add_filter( 'pll_copy_post_metas', 'commercegurus_attributes_gallary_pll_copy_post_metas', 10, 5 );
