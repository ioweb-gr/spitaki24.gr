<?php

namespace WPML\Nav\Infrastructure\Repository;

use WPML\FP\Logic;
use WPML\Nav\Application\Repository\TranslationRepositoryInterface;
use WPML\Nav\Infrastructure\Adapter\WPML\Translation as TranslationAdapter;

class TranslationRepository implements TranslationRepositoryInterface
{
	/** @var SettingsRepository */
	private $settingsRepository;

	/** @var TranslationAdapter  */
	private $translationAdapter;

	/**
	 * @param SettingsRepository $settingsRepository
	 * @param TranslationAdapter $translation
	 */
	public function __construct( SettingsRepository $settingsRepository, TranslationAdapter $translationAdapter ) {
		$this->settingsRepository = $settingsRepository;
		$this->translationAdapter = $translationAdapter;
	}

	public function getOriginalPostId( $postId ) {
		$originalPostId = $this->translationAdapter->getOriginalPostId( $postId );
		return $originalPostId ?: $postId;
	}

	public function getTranslatedPostId( $originalPostId, $currentLanguage, $defaultLanguage ) {
		$isFallbackEnabled = $this->settingsRepository->isPostTypeDisplayedAsTranslate( 'page' );
		$displayedPostId = $this->translationAdapter->getTranslationForPost( $originalPostId, $currentLanguage, $isFallbackEnabled ? $defaultLanguage : null );

		if ( ! $displayedPostId && $isFallbackEnabled  ) {
			return (int) $originalPostId;
		}
		return (int) $displayedPostId;
	}

}