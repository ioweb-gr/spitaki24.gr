<?php
namespace WPML\Nav\Application\Repository;

interface TranslationRepositoryInterface {

	/**
	 * Checks if the current ID belongs to a translated post, and returns original post ID if true.
	 *
	 * @param int $postId
	 * @param string $language
	 * @return int
	 */
	public function getOriginalPostId( $postId );

	/**
	 * @param int $originalPostId
	 * @param string $currentLanguage
	 * @param string $defaultLanguage
	 * @return int|null
	 */
	public function getTranslatedPostId( $originalPostId, $currentLanguage, $defaultLanguage );

}
