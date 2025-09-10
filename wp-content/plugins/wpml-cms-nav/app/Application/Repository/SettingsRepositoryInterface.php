<?php
namespace WPML\Nav\Application\Repository;

use WPML\Nav\Domain\Settings;

interface SettingsRepositoryInterface {

	/**
	 * @return Settings
	 */
	public function getSettings();

	/**
	 * @param string $postType
	 * @return bool
	 */
	public function isPostTypeDisplayedAsTranslate( $postType );
}
