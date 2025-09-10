<?php

namespace WPML\Nav\Application;

use WPML\Nav\Application\Filter\SidebarSectionsFilterInterface as SidebarSectionsFilter;
use WPML\Nav\Application\Repository\TranslationRepositoryInterface;
use WPML\Nav\Domain\Navigation\Sidebar;
use WPML\Nav\Domain\Post;
use WPML\Nav\Application\Repository\PostRepositoryInterface as PostRepository;
use WPML\Nav\Application\Repository\TranslationRepositoryInterface as TranslationRepository;
use WPML\Nav\Application\Repository\SettingsRepositoryInterface as SettingsRepository;
use WPML\Nav\Application\Repository\NavigationRepositoryInterface as NavigationRepository;
use WPML\Nav\Domain\Settings;

class PageNaviagtion {

	/** @var PostRepository  */
	private $postRepository;

	/** @var TranslationRepository  */
	private $translationRepository;

	/** @var SettingsRepository */
	private $settingsRepository;

	/** @var NavigationRepository  */
	private $navigationRepository;

	/** @var SidebarSectionsFilter */
	private $sidebarSectionsFilter;

	/**
	 * @param PostRepository $postRepository
	 * @param SettingsRepository $settingsRepository
	 * @param NavigationRepository $navigationRepository
	 * @param TranslationRepository $translationRepository
	 * @param SidebarSectionsFilter $sidebarSectionsFilter
	 */
    public function __construct(
		PostRepository $postRepository,
		SettingsRepository $settingsRepository,
		NavigationRepository $navigationRepository,
		TranslationRepositoryInterface $translationRepository,
		SidebarSectionsFilter $sidebarSectionsFilter
	) {
		$this->postRepository = $postRepository;
		$this->settingsRepository = $settingsRepository;
		$this->navigationRepository = $navigationRepository;
		$this->translationRepository = $translationRepository;
		$this->sidebarSectionsFilter = $sidebarSectionsFilter;
    }

	/**
	 * @return Post
	 */
	public function getGlobalPost() {
		return $this->postRepository->getGlobalPost();
	}

	/**
	 * @param Post $post
	 * @param string $languageCode
	 * @param string $defaultLanguageCode
	 * @return Sidebar
	 */
	public function getSidebar( $post, $languageCode, $defaultLanguageCode ) {
		$rootPost = $this->getCurrentRootPost( $post );
		$settings = $this->settingsRepository->getSettings();

		$sectionsArray = $this->getChildTranslatedPostIdsGroupedBySections( $rootPost, $languageCode, $defaultLanguageCode, $settings );

		$sectionsArray = $this->sidebarSectionsFilter->filter( $sectionsArray );

		return $this->navigationRepository->getSidebarNavigation( $rootPost, $sectionsArray, $languageCode, $defaultLanguageCode, $settings );
	}

	/**
	 * Get the Root post shown in the pages navigation, given a current post.
	 *
	 * @param Post $globalPost
	 * @return Post|null
	 */
	private function getCurrentRootPost( $globalPost ) {
		if ( $globalPost->isMinihome() || ! $globalPost->hasAncestors() ) {
			return $globalPost;
		}

		return $this->postRepository->getHighestAncestorOrMinihome( $globalPost );
	}

	/**
	 * @param Post $parentPost
	 * @param string $currentLanguage
	 * @param string $defaultLanguage
	 * @param Settings $settings
	 * @return array<string, int[]>
	 */
	private function getChildTranslatedPostIdsGroupedBySections( $parentPost, $currentLanguage, $defaultLanguage, $settings ) {
		if ( $this->settingsRepository->isPostTypeDisplayedAsTranslate( 'page' ) ) {
			$originalRootPostId = $this->translationRepository->getOriginalPostId( $parentPost->getId() );
		} else {
			$originalRootPostId = $parentPost->getId();
		}
		$childPosts = $this->postRepository->getChildPosts( $originalRootPostId, $settings->getPageOrder() );

		$sections = [];
		foreach ( $childPosts as $childPost ) {
			$displayedPostId = $this->translationRepository->getTranslatedPostId( $childPost->getId(), $currentLanguage, $defaultLanguage );
			if ( $displayedPostId ) {
				$sections[ $childPost->getSection() ][] = (int) $displayedPostId;
			}
		}
		ksort( $sections );
		return $sections;
	}
}