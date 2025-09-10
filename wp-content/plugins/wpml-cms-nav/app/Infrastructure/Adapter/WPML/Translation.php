<?php
namespace WPML\Nav\Infrastructure\Adapter\WPML;

use WPML\FP\Obj;
use WPML\Element\API\PostTranslations;

class Translation {

	/**
	 * @param int $postId
	 * @param string $type
	 * @return int
	 */
	public function getOriginalPostId( $postId, $type = 'post_page' ) {
		return PostTranslations::getOriginalId( $postId, $type );
	}

	/**
	 * @param int $postId
	 * @param string $language_code
	 * @param string $fallback_language_code
	 * @return int|null
	 */
	public function getTranslationForPost( $postId, $language_code, $fallback_language_code = null ) {
		$translations = PostTranslations::get( $postId );
		$translation = Obj::prop( $language_code, $translations );
		if ( null === $translation && null !== $fallback_language_code ) {
			$translation = Obj::prop( $fallback_language_code, $translations );
		}
		return null === $translation ? null : Obj::prop( 'element_id', $translation );
	}

}

?>