<?php

namespace WPML\Nav\Infrastructure\Repository;

use WPML\FP\Obj;
use WPML\Nav\Application\Repository\SettingsRepositoryInterface;
use WPML\Nav\Domain\Settings;
use WPML\Nav\Infrastructure\Adapter\WPML\Settings as WPMLSettingsAdapter;

class SettingsRepository implements SettingsRepositoryInterface
{
	/**
	 * @var WPMLSettingsAdapter
	 */
	private $settingsAdapter;

	/**
	 * @param WPMLSettingsAdapter $settingsAdapter
	 */
	public function __construct( WPMLSettingsAdapter $settingsAdapter)
	{
		$this->settingsAdapter = $settingsAdapter;
	}

	/**
	 * @return Settings
	 */
	public function getSettings() {
		$settingsArray = get_option( 'wpml_cms_nav_settings' );

		// Use WPML legacy. Read settings from WPML if they exist there.
		if ( empty( $settingsArray ) ) {
			require_once WPML_CMS_NAV_PLUGIN_PATH . '/inc/cms-navigation-schema.php';
			$settingsArray = wpml_cms_nav_default_settings();
		}

		// Check if cache is disabled by const.
		if ( defined( 'WPML_CMS_NAV_DISABLE_CACHE' ) && WPML_CMS_NAV_DISABLE_CACHE ) {
			$settingsArray[ 'cache' ] = false;
		}

		return new Settings(
			Obj::propOr( true, 'cache', $settingsArray ),
			Obj::propOr( 'menu_order', 'page_order', $settingsArray ),
			Obj::propOr( '<h4>', 'heading_start', $settingsArray ),
			Obj::propOr( '</h4>', 'heading_end', $settingsArray )
		);
	}

	/**
	 * @param string $postType
	 * @return bool
	 */
	public function isPostTypeDisplayedAsTranslate( $postType ) {
		return $this->settingsAdapter->isPostTypeDisplayedAsTranslate( $postType );
	}

}