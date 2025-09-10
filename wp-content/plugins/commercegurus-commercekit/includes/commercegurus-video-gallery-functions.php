<?php
/**
 * Main functions for rendering video gallery
 *
 * @author   CommerceGurus
 * @package  CommerceGurus_Video_Gallery
 * @since    1.0.0
 */

/**
 * Get product video gallery admin html.
 */
function commercegurus_get_video_gallery_html() {
	global $post;
	$product_id = $post->ID;
	require_once dirname( __FILE__ ) . '/templates/admin-product-video-gallery.php';
}
add_filter( 'woocommerce_product_data_panels', 'commercegurus_get_video_gallery_html' );

/**
 * Save product video gallery
 *
 * @param string $post_id post ID.
 * @param string $post post.
 */
function commercegurus_save_product_video_gallery( $post_id, $post ) {
	if ( 'product' !== $post->post_type ) {
		return;
	}
	$commercekit_video = isset( $_POST['commercekit_video'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_video'] ) ) : '';
	if ( $commercekit_video && wp_verify_nonce( $commercekit_video, 'commercekit_video' ) ) {
		if ( $post_id ) {
			$cgkit_video_gallery = isset( $_POST['commercekit_wc_video_gallery'] ) ? map_deep( wp_unslash( $_POST['commercekit_wc_video_gallery'] ), 'sanitize_textarea_field' ) : array();
			update_post_meta( $post_id, 'commercekit_wc_video_gallery', array_filter( $cgkit_video_gallery ) );
		}
	}
}
add_action( 'woocommerce_process_product_meta', 'commercegurus_save_product_video_gallery', 10, 2 );

/**
 * Get product video gallery
 *
 * @param string $post_id post ID.
 * @param string $attachment_id attachment id.
 */
function commercegurus_get_product_video_gallery( $post_id, $attachment_id ) {
	$videos    = get_post_meta( $post_id, 'commercekit_wc_video_gallery', true );
	$video_url = isset( $videos[ $attachment_id ] ) ? $videos[ $attachment_id ] : '';
	$css_class = ! empty( $video_url ) ? 'cgkit-editvideos' : 'cgkit-addvideos';
	echo '<span class="dashicons dashicons-video-alt3 cgkit-videos ' . esc_attr( $css_class ) . '"><input type="hidden" class="cgkit-video-gallery" name="commercekit_wc_video_gallery[' . esc_attr( $attachment_id ) . ']" value="' . esc_url( $video_url ) . '" /></span>';
}
add_action( 'woocommerce_admin_after_product_gallery_item', 'commercegurus_get_product_video_gallery', 10, 2 );

/**
 * Add admin CSS and JS scripts
 */
function commercegurus_product_video_admin_scripts() {
	$screen = get_current_screen();
	if ( 'product' === $screen->post_type && 'post' === $screen->base ) {
		wp_enqueue_style( 'commercekit-video-gallery-style', CKIT_URI . 'assets/css/admin-product-video-gallery.css', array(), CGKIT_CSS_JS_VER );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'commercekit-video-admin-script', CKIT_URI . 'assets/js/admin-product-video-gallery.js', array(), CGKIT_CSS_JS_VER, true );
	}
}
add_action( 'admin_enqueue_scripts', 'commercegurus_product_video_admin_scripts' );

/**
 * Product gallery options
 *
 * @param string $options module options.
 */
function commercekit_get_gallery_options( $options ) {
	global $post;
	$commercekit_pdp   = array();
	$commercekit_flags = commercekit_feature_flags()->get_flags();
	$pdp_thumbnails    = isset( $options['pdp_desktop_thumbnails'] ) && ! empty( $options['pdp_desktop_thumbnails'] ) ? (int) $options['pdp_desktop_thumbnails'] : (int) commercekit_get_default_settings( 'pdp_desktop_thumbnails' );
	$pdp_m_thumbs      = isset( $options['pdp_mobile_thumbnails'] ) && ! empty( $options['pdp_mobile_thumbnails'] ) ? (int) $options['pdp_mobile_thumbnails'] : (int) commercekit_get_default_settings( 'pdp_mobile_thumbnails' );
	$pdp_thumbnails_o  = (int) $pdp_thumbnails;

	$commercekit_pdp['pdp_thumbnails'] = apply_filters( 'commercekit_product_gallery_thumbnails', $pdp_thumbnails );
	$pdp_m_thumbs                      = $pdp_thumbnails_o !== (int) $commercekit_pdp['pdp_thumbnails'] ? (int) $commercekit_pdp['pdp_thumbnails'] : $pdp_m_thumbs;
	$commercekit_pdp['pdp_m_thumbs']   = apply_filters( 'commercekit_product_mobile_gallery_thumbnails', $pdp_m_thumbs );
	$commercekit_pdp['pdp_v_thumbs']   = apply_filters( 'commercekit_product_vertical_gallery_thumbnails', 5 );
	$commercekit_pdp['pdp_lightbox']   = ( ( isset( $options['pdp_lightbox'] ) && 1 === (int) $options['pdp_lightbox'] ) || ! isset( $options['pdp_lightbox'] ) ) ? 1 : 0;

	$commercekit_pdp['pdp_lightbox_cap']   = isset( $options['pdp_lightbox_cap'] ) && 1 === (int) $options['pdp_lightbox_cap'] ? 1 : 0;
	$commercekit_pdp['pdp_gallery_layout'] = isset( $options['pdp_gallery_layout'] ) && ! empty( $options['pdp_gallery_layout'] ) ? $options['pdp_gallery_layout'] : commercekit_get_default_settings( 'pdp_gallery_layout' );

	if ( function_exists( 'is_product' ) && is_product() ) {
		if ( isset( $post->ID ) && $post->ID ) {
			$cgkit_gallery_layout = get_post_meta( $post->ID, 'commercekit_gallery_layout', true );
			if ( ! empty( $cgkit_gallery_layout ) ) {
				$commercekit_pdp['pdp_gallery_layout'] = $cgkit_gallery_layout;
			}
		}
	}
	$commercekit_pdp['pdp_sticky_atc'] = 0;

	$cgkit_sticky_hdr_class = apply_filters( 'commercekit_sticky_header_css_class', 'body.sticky-m header.site-header' );
	if ( empty( $cgkit_sticky_hdr_class ) ) {
		$cgkit_sticky_hdr_class = 'body.sticky-m header.site-header';
	}
	$commercekit_pdp['cgkit_sticky_hdr_class'] = $cgkit_sticky_hdr_class;

	$sticky_atc_desktop = isset( $commercekit_flags['sticky_atc_desktop'] ) && 1 === (int) $commercekit_flags['sticky_atc_desktop'] ? 1 : 0;
	$sticky_atc_mobile  = isset( $commercekit_flags['sticky_atc_mobile'] ) && 1 === (int) $commercekit_flags['sticky_atc_mobile'] ? 1 : 0;
	$sticky_atc_tabs    = isset( $commercekit_flags['sticky_atc_tabs'] ) && 1 === (int) $commercekit_flags['sticky_atc_tabs'] ? 1 : 0;
	if ( $sticky_atc_desktop || $sticky_atc_mobile || $sticky_atc_tabs ) {
		$commercekit_pdp['pdp_sticky_atc'] = 1;
	}
	$commercekit_pdp['pdp_mobile_layout']    = isset( $options['pdp_mobile_layout'] ) ? $options['pdp_mobile_layout'] : commercekit_get_default_settings( 'pdp_mobile_layout' );
	$commercekit_pdp['pdp_showedge_percent'] = (string) commercekit_gallery_pdp_showedge_percent( false );

	$commercekit_pdp['pdp_json_data']  = defined( 'CGKIT_PDP_SINGLE_JSON' ) && true === CGKIT_PDP_SINGLE_JSON ? 1 : 0;
	$commercekit_pdp['pdp_gal_loaded'] = 0;

	return $commercekit_pdp;
}

/**
 * Gallery show edge layout percent
 *
 * @param  string $css CSS percent.
 */
function commercekit_gallery_pdp_showedge_percent( $css = true ) {
	$options = get_option( 'commercekit', array() );
	$percent = isset( $options['next_slide_percent'] ) ? (int) $options['next_slide_percent'] : (int) commercekit_get_default_settings( 'next_slide_percent' );
	$styles  = array(
		10 => '91',
		20 => '83',
		30 => '76',
	);
	$swipers = array(
		10 => '1.1',
		20 => '1.2',
		30 => '1.3',
	);
	if ( $css ) {
		if ( isset( $styles[ $percent ] ) ) {
			return $styles[ $percent ];
		} else {
			return '91';
		}
	} else {
		if ( isset( $swipers[ $percent ] ) ) {
			return $swipers[ $percent ];
		} else {
			return '1.1';
		}
	}
}

/**
 * Admin gallery featured review meta box.
 */
function commercekit_gallery_featured_review_meta_box() {
	$options = get_option( 'commercekit', array() );
	if ( isset( $options['pdp_featured_review'] ) && 1 === (int) $options['pdp_featured_review'] ) {
		add_meta_box( 'commercekit-gallery-featured-review-meta-box', esc_html__( 'CommerceKit Gallery Featured Review', 'commercegurus-commercekit' ), 'commercekit_gallery_featured_review_meta_display', 'product', 'normal', 'low' );
	}
}
add_action( 'admin_init', 'commercekit_gallery_featured_review_meta_box' );

/**
 * Admin gallery featured review meta box display.
 *
 * @param string $post post object.
 */
function commercekit_gallery_featured_review_meta_display( $post ) {
	$cgkit_pdp_review = array();
	if ( isset( $post->ID ) && $post->ID && 'auto-draft' !== $post->post_status ) {
		$cgkit_pdp_review = (array) get_post_meta( $post->ID, 'cgkit_pdp_review', true );
	}
	require_once dirname( __FILE__ ) . '/templates/admin-pdp-featured-review.php';
}

/**
 * Admin gallery featured review meta box save.
 *
 * @param string $post_id post id.
 * @param string $post post object.
 */
function commercekit_gallery_featured_review_meta_save( $post_id, $post ) {
	if ( 'product' === $post->post_type ) {
		$options = get_option( 'commercekit', array() );
		if ( isset( $options['pdp_featured_review'] ) && 1 === (int) $options['pdp_featured_review'] ) {
			$commercekit_nonce = isset( $_POST['commercekit_nonce2'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce2'] ) ) : '';
			if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
				return;
			}
			$cgkit_pdp_review = isset( $_POST['cgkit_pdp_review'] ) ? map_deep( wp_unslash( $_POST['cgkit_pdp_review'] ), 'wp_kses_post' ) : array(); // phpcs:ignore
			update_post_meta( $post->ID, 'cgkit_pdp_review', $cgkit_pdp_review );
		}
	}
}
add_action( 'save_post', 'commercekit_gallery_featured_review_meta_save', 10, 2 );

/**
 * Gallery featured review for desktop.
 */
function commercekit_gallery_featured_review_desktop() {
	commercekit_gallery_featured_review_display( 'desktop' );
}
add_action( 'commercekit_after_gallery', 'commercekit_gallery_featured_review_desktop', 99 );

/**
 * Gallery featured review for mobile.
 */
function commercekit_gallery_featured_review_mobile() {
	commercekit_gallery_featured_review_display( 'mobile' );
}
add_action( 'woocommerce_single_product_summary', 'commercekit_gallery_featured_review_mobile', 85 );

/**
 * Gallery featured review display.
 *
 * @param string $type type of display.
 */
function commercekit_gallery_featured_review_display( $type ) {
	global $product;
	$options = get_option( 'commercekit', array() );
	if ( isset( $options['pdp_featured_review'] ) && 1 === (int) $options['pdp_featured_review'] && $product ) {
		$product_id = is_object( $product ) && method_exists( $product, 'get_id' ) ? $product->get_id() : 0;
		$pdp_review = (array) get_post_meta( $product_id, 'cgkit_pdp_review', true );
		if ( ! isset( $pdp_review['text'] ) || empty( $pdp_review['text'] ) ) {
			return '';
		}
		$review_image  = '';
		$attachment_id = isset( $pdp_review['image'] ) && ! empty( $pdp_review['image'] ) ? (int) $pdp_review['image'] : 0;
		if ( $attachment_id ) {
			$gal_thumb  = wc_get_image_size( 'gallery_thumbnail' );
			$image_size = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gal_thumb['width'], $gal_thumb['height'] ) );
			$image_size = apply_filters( 'commercegurus_woocommerce_gallery_thumbnail_size', $image_size );
			$thumb_src  = wp_get_attachment_image_src( $attachment_id, $image_size );
			if ( $thumb_src ) {
				$review_image = wp_get_attachment_image(
					$attachment_id,
					$image_size,
					false,
					apply_filters(
						'woocommerce_gallery_image_html_attachment_image_params',
						array(
							'title'        => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
							'data-caption' => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
							'class'        => 'featured-review-image',
						),
						$attachment_id,
						$image_size,
						true
					)
				);
			}
		}
		?>	
<div class="cgkit-pdp-review cgkit-pdp-review-<?php echo esc_attr( $type ); ?>">
	<div class="cgkit-pdp-review--image"><?php echo $review_image; // phpcs:ignore ?></div>
	<div class="cgkit-pdp-review--text"><?php echo wp_kses_post( $pdp_review['text'] ); ?></div>
</div>
<style>
.cgkit-pdp-review.cgkit-pdp-review-mobile { margin-top: 1em; margin-bottom: 1em; align-items:flex-start; border-radius:5px; }
.cgkit-pdp-review-mobile .cgkit-pdp-review--text { margin-top: -5px }
.cgkit-pdp-review-desktop { margin-top: 1rem; }
.cgkit-pdp-review { display:flex; width:100%; align-items: center; padding:1.5rem; background:#fff; border:1px solid #e2e2e2; border-radius:8px;}
.cgkit-pdp-review--image:has(img), .cgkit-pdp-review--image:has(picture) { width: 75px; min-width: 75px; margin-right:1.5rem; }
.cgkit-pdp-review--image img { width: 100%; height: auto;  }
.cgkit-pdp-review--text { color: #222; font-size: clamp(0.875rem, 0.8115rem + 0.2033vw, 0.9375rem); }
.cgkit-pdp-review--text mark {background: transparent;background-image: linear-gradient(90deg, rgba(255, 225, 0, .1), rgba(255, 225, 0, .7) 4%, rgba(255, 225, 0, .3));border-radius: .8rem .3rem;-webkit-box-decoration-break: clone;box-decoration-break: clone;margin: 0 -0.4rem;padding: 0.1rem 0.4rem;
}
.rtl .cgkit-pdp-review--image:has(img),
.rtl .cgkit-pdp-review--image:has(picture) { margin-left:1.5rem; margin-right:0 }
@media (max-width: 770px) { .cgkit-pdp-review-desktop { display: none; } }
@media (min-width: 771px) { .cgkit-pdp-review-mobile { display: none; } }
</style>
		<?php
	}
}
