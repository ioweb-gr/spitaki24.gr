<?php
namespace WPML\Nav\Application\Repository;

use WPML\Nav\Domain\Post;
use WPML\Nav\Domain\Navigation\Sidebar;
use WPML\Nav\Domain\Settings;

interface NavigationRepositoryInterface {

	/**
	 * @param Post $parentPost
	 * @param int[][] $sectionsArray
	 * @param string $currentLanguage
	 * @param string $defaultLanguage
	 * @param Settings $settings
	 * @return Sidebar
	 */
	public function getSidebarNavigation($parentPost, $sectionsArray, $currentLanguage, $defaultLanguage, $settings );
}
