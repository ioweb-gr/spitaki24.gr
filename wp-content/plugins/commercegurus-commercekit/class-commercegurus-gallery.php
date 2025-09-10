<?php
/**
 * CommerceGurus Gallery
 *
 * @author   CommerceGurus
 * @package  CommerceGurus_Gallery
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

/**
 * Required minimums and constants
 */
require_once CGKIT_BASE_PATH . 'includes/commercegurus-gallery-functions.php';
require_once CGKIT_BASE_PATH . 'includes/commercegurus-video-gallery-functions.php';

/**
 * Main CommerceGurus_Gallery Class
 *
 * @class CommerceGurus_Gallery
 * @version 2.0.0
 * @since 1.0.0
 * @package CommerceGurus_Gallery
 */
if ( ! class_exists( 'CommerceGurus_Gallery' ) && ( ! defined( 'COMMERCEKIT_PDP_GALLERY_VISIBLE' ) || COMMERCEKIT_PDP_GALLERY_VISIBLE !== false ) ) {
	/**
	 * Main class.
	 */
	class CommerceGurus_Gallery {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		const VERSION = '2.0.0';

		/**
		 * Notices (array)
		 *
		 * @var array
		 */
		public $notices = array();

		/**
		 * Shortcode (bool)
		 *
		 * @var is_shortcode
		 */
		public $is_shortcode = false;

		/**
		 * Main constructor.
		 */
		public function __construct() {
			$options            = get_option( 'commercekit', array() );
			$this->is_shortcode = isset( $options['widget_pos_pdp_gallery'] ) && 1 === (int) $options['widget_pos_pdp_gallery'] ? true : false;
			$this->init();
		}

		/**
		 * Init the plugin after plugins_loaded so environment variables are set.
		 */
		public function init() {
			/**
			 * Init all the things.
			 */
			add_action( 'wp', array( $this, 'commercegurus_init_gallery' ) );
			add_action( 'woocommerce_before_single_product', array( $this, 'commercegurus_unhook_core_gallery' ) );
			if ( $this->is_shortcode ) {
				add_shortcode( 'commercekit_product_gallery', array( $this, 'commercegurus_load_pdp_gallery' ) );
				add_action( 'elementor/widgets/register', 'commercekit_product_gallery_elementor_widget' );
			} else {
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'commercegurus_load_pdp_gallery' ), 20 );
			}
			add_action( 'wp_enqueue_scripts', array( $this, 'commercegurus_gallery_assets' ) );
		}

		/**
		 * Frontend: Load all scripts and styles.
		 */
		public function commercegurus_gallery_assets() {
			global $post;
			if ( $this->commercegurus_cant_display() ) {
				return;
			}
			$options      = get_option( 'commercekit', array() );
			$pdp_lightbox = ( ( isset( $options['pdp_lightbox'] ) && 1 === (int) $options['pdp_lightbox'] ) || ! isset( $options['pdp_lightbox'] ) ) ? true : false;
			$load_swiper  = true;

			$pdp_gallery_layout = isset( $options['pdp_gallery_layout'] ) && ! empty( $options['pdp_gallery_layout'] ) ? $options['pdp_gallery_layout'] : commercekit_get_default_settings( 'pdp_gallery_layout' );
			if ( function_exists( 'is_product' ) && is_product() && $post ) {
				$cgkit_gallery_layout2 = get_post_meta( $post->ID, 'commercekit_gallery_layout', true );
				if ( ! empty( $cgkit_gallery_layout2 ) ) {
					$pdp_gallery_layout = $cgkit_gallery_layout2;
				}
			}

			if ( 'horizontal' === $pdp_gallery_layout || 'vertical-left' === $pdp_gallery_layout || 'vertical-right' === $pdp_gallery_layout ) {
				$load_swiper = true;
			}

			if ( function_exists( 'is_product' ) && is_product() ) {
				if ( $load_swiper ) {
					wp_enqueue_script( 'commercegurus-swiperjs', plugins_url( 'assets/js/swiper-bundle.min.js', __FILE__ ), array(), CGKIT_CSS_JS_VER, true );
				}

				if ( $pdp_lightbox ) {
					wp_enqueue_script( 'commercegurus-photoswipe', plugins_url( 'assets/js/photoswipe.min.js', __FILE__ ), array(), CGKIT_CSS_JS_VER, true );
					wp_enqueue_script( 'commercegurus-photoswipe-ui-default', plugins_url( 'assets/js/photoswipe-ui-default.min.js', __FILE__ ), array(), CGKIT_CSS_JS_VER, true );
				}

				wp_enqueue_script( 'commercegurus-gallery', plugins_url( 'assets/js/commercegurus-gallery.js', __FILE__ ), array(), CGKIT_CSS_JS_VER, true );
				if ( $load_swiper ) {
					wp_enqueue_style( 'commercegurus-swiperjscss', plugins_url( 'assets/css/swiper-bundle.min.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
				}

				if ( $pdp_lightbox ) {
					wp_enqueue_style( 'commercegurus-photoswipe', plugins_url( 'assets/css/photoswipe.min.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
					wp_enqueue_style( 'commercegurus-photoswipe-skin', plugins_url( 'assets/css/default-skin.min.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
				}
			}
		}

		/**
		 * Frontend: Remove core wc gallery.
		 */
		public function commercegurus_unhook_core_gallery() {
			if ( $this->commercegurus_cant_display() ) {
				return;
			}
			if ( ! $this->is_shortcode ) {
				remove_action( 'woocommerce_after_single_product', 'shoptimizer_pdp_gallery_modal_fix' );
				remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 10 );
				remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
			}
		}

		/**
		 * Frontend: Load CommerceGurus Gallery.
		 */
		public function commercegurus_load_pdp_gallery() {
			global $product;
			if ( $this->commercegurus_cant_display() ) {
				return '';
			}
			if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) ) {
				return '';
			}
			if ( $this->is_shortcode ) {
				ob_start();
			}
			$options = get_option( 'commercekit', array() );

			$pdp_gallery_layout    = isset( $options['pdp_gallery_layout'] ) && ! empty( $options['pdp_gallery_layout'] ) ? $options['pdp_gallery_layout'] : commercekit_get_default_settings( 'pdp_gallery_layout' );
			$cgkit_gallery_layout2 = get_post_meta( $product->get_id(), 'commercekit_gallery_layout', true );
			if ( ! empty( $cgkit_gallery_layout2 ) ) {
				$pdp_gallery_layout = $cgkit_gallery_layout2;
			}

			if ( 'horizontal' === $pdp_gallery_layout || 'vertical-left' === $pdp_gallery_layout || 'vertical-right' === $pdp_gallery_layout ) {
				require CGKIT_BASE_PATH . 'includes/pdp-gallery-swiper.php';
			} elseif ( 'vertical-scroll' === $pdp_gallery_layout || 'simple-scroll' === $pdp_gallery_layout ) {
				require CGKIT_BASE_PATH . 'includes/pdp-gallery-scroll.php';
			} else {
				require CGKIT_BASE_PATH . 'includes/pdp-gallery-grid.php';
			}
			if ( $this->is_shortcode ) {
				$gallery_html = ob_get_contents();
				ob_end_clean();
				return $gallery_html;
			}
		}

		/**
		 * Useful function for doing global tweaks (like removing core lazy filters.
		 */
		public function commercegurus_init_gallery() {
			if ( $this->commercegurus_cant_display() ) {
				return;
			}
			if ( ! $this->is_shortcode ) {
				remove_theme_support( 'wc-product-gallery-lightbox' );
				remove_theme_support( 'wc-product-gallery-zoom' );
				remove_theme_support( 'wc-product-gallery-slider' );
			}
		}

		/**
		 * Disable this gallery when WC 360 Image enabled.
		 */
		public function commercegurus_cant_display() {
			global $post;
			if ( class_exists( 'WC_360_Image_Display' ) ) {
				$wc360 = new WC_360_Image_Display();
				if ( method_exists( $wc360, 'display_bool' ) && $wc360->display_bool() ) {
					return true;
				}
			}
			if ( function_exists( 'is_product' ) && is_product() && $post ) {
				$cgkit_gallery_layout = get_post_meta( $post->ID, 'commercekit_gallery_layout', true );
				if ( 'core-gallery' === $cgkit_gallery_layout ) {
					return true;
				}
			}
			return false;
		}
	}

	$commercegurus_gallery = new CommerceGurus_Gallery();
}
