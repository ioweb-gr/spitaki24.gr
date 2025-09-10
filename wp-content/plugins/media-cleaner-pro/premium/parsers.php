<?php

class MeowPro_WPMC_Parsers {

	public function __construct() {

		// ACF
		if ( class_exists( 'ACF' ) )
			require_once( 'parsers/acf.php' );

		// ACF Widgets
		if ( function_exists( 'acfw_globals' ) )  // mm change
			require_once( 'parsers/acf_widgets.php' );

		// Divi (ElegantThemes)
		if ( function_exists( '_et_core_find_latest' ) )
			require_once( 'parsers/divi.php' );

		// Visual Composer (WPBakery)
		if ( class_exists( 'Vc_Manager' ) )
			require_once( 'parsers/wpbakery_vc.php' );

		// Fusion Builder (Avada)
		if ( function_exists( 'fusion_builder_map' ) )
			require_once( 'parsers/fusion_builder.php' );

		// Elementor
		if ( function_exists( 'elementor_load_plugin_textdomain' ) )
			require_once( 'parsers/elementor.php' );

		// Beaver Builders
		if ( class_exists( 'FLBuilderModel' ) )
			require_once( 'parsers/beaver_builder.php' );

		// Oxygen Builder
		if ( class_exists( 'Oxygen_VSB_Dynamic_Shortcodes' ) )
			require_once( 'parsers/oxygen_builder.php' );

		// Brizy
		if ( class_exists( 'Brizy_Editor_Post' ) )
			require_once( 'parsers/brizy.php' );

		// ZipList Recipe
		if ( function_exists( 'amd_zlrecipe_convert_to_recipe' ) )
			require_once( 'parsers/ziplist_recipe.php' );

		// UberMenu
		if ( class_exists( 'UberMenu' ) )
		require_once( 'parsers/ubermenu.php' );

		// X Theme
		if ( class_exists( 'X_Bootstrap' ) )
			require_once( 'parsers/theme-x.php' );

		// Easy Real Estate
		if ( class_exists( 'Easy_Real_Estate' ) )
			require_once( 'parsers/easy_real_estate.php' );

		// Tasty Pins
		if ( defined( 'TASTY_PINS_PLUGIN_FILE' ) )
			require_once( 'parsers/tasty_pins.php' );

		// WCFM MarketPlace
		if ( class_exists( 'WCFMmp' ) )
			require_once( 'parsers/wcfm_marketplace.php' );

		// Revolution Slider
		if ( class_exists( 'RevSliderFront' ) )
			require_once( 'parsers/revslider.php' );

		// WP Residence
		if ( defined( 'WPESTATE_PLUGIN_URL' ) )
			require_once( 'parsers/wpresidence.php' );

		// Avia Framework
		if ( defined( 'AV_FRAMEWORK_VERSION' ) ) {
			require_once( 'parsers/avia_framework.php' );
		}

		// Fat Portfolio
		if ( class_exists( 'FAT_Portfolio' ) ) {
			require_once( 'parsers/fat_portfolio.php' );
		}

		// Yikes Custom Product Tabs
		if ( class_exists( 'YIKES_Custom_Product_Tabs' ) ) {
			require_once( 'parsers/custom_product_tabs.php' );
		}

		// Directories (SabaiApps)
		if ( function_exists( 'drts' ) ) {
			require_once( 'parsers/directories.php' );
		}

		// Image Map Pro
		if ( class_exists( 'ImageMapPro' ) ) {
			require_once( 'parsers/image_map_pro.php' );
		}

		// YooTheme Builder
		if ( class_exists( 'YOOtheme\\Builder' ) ) {
			require_once( 'parsers/yootheme_builder.php' );
		}

		// GeoDirectory
		if ( class_exists( 'geodirectory' ) ) {
			require_once( 'parsers/geodirectory.php' );
		}

		// Modula Gallery
		if ( class_exists( 'Modula' ) ) {
			require_once( 'parsers/modula_gallery.php' );
		}

		// Justified Image Grid
		if ( class_exists( 'JustifiedImageGrid' ) ) {
			require_once( 'parsers/justified_image_grid.php' );
		}

		// Advanced Ads
		if ( class_exists( 'Advanced_Ads' ) ) {
			require_once( 'parsers/advanced_ads.php' );
		}

		// Download Monitor
		if ( class_exists( 'WP_DLM' ) ) {
			require_once( 'parsers/download_monitor.php' );
		}

	}

}
