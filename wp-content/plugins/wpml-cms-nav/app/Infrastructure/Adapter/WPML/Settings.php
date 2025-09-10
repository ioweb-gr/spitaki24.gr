<?php
namespace WPML\Nav\Infrastructure\Adapter\WPML;

class Settings {
	/**
	 * @var
	 */
	private $sitepress;

	/**
	 * @param \SitePress $sitepress
	 */
	public function __construct( \SitePress $sitepress = null ) {
		$this->sitepress = $sitepress;
	}

	/**
	 * @param string $post_type
	 * @return bool
	 */
	public function isPostTypeDisplayedAsTranslate( $post_type ) {
		return $this->sitepress->is_display_as_translated_post_type( $post_type );
	}
}

?>