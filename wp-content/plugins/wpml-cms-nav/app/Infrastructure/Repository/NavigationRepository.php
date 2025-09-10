<?php

namespace WPML\Nav\Infrastructure\Repository;

use WPML\Nav\Application\Repository\NavigationRepositoryInterface;

use WPML\Nav\Domain\Navigation\Item;
use WPML\Nav\Domain\Navigation\Sidebar;
use WPML\Nav\Domain\Navigation\Section;
use WPML\Nav\Domain\Settings;

use WPML\Nav\Infrastructure\Adapter\WP\PostRepository as PostRepositoryWPAdapter;

class NavigationRepository implements NavigationRepositoryInterface {
	/** @var PostRepository  */
	private $postRepository;

	/** @var PostRepositoryWPAdapter  */
	private $postRepositoryWPAdapter;

	/** @var TranslationRepository */
	private $translationRepository;

	/**
	 * @param PostRepository $postRepository
	 * @param TranslationRepository $translationRepository
	 * @param PostRepositoryWPAdapter $postRepositoryWPAdapter
	 */
	public function __construct(
		PostRepository $postRepository,
		TranslationRepository $translationRepository,
		PostRepositoryWPAdapter $postRepositoryWPAdapter
	) {
		$this->postRepository = $postRepository;
		$this->translationRepository = $translationRepository;
		$this->postRepositoryWPAdapter = $postRepositoryWPAdapter;
	}

	public function getSidebarNavigation( $parentPost, $sectionsArray, $currentLanguage, $defaultLanguage, $settings ) {
		$sections = [];
		foreach ( $sectionsArray as $title => $postIds ) {
			$items = [];
			foreach( $postIds as $postId ) {
				$childItems = $this->getChildItems( $postId, $settings, $currentLanguage, $defaultLanguage );
				$items[] = $this->getItemByPostId( $postId, $childItems );
			}
			$sections[] = new Section( $title, $items );
		}

		$displayedItemId = $this->translationRepository->getTranslatedPostId( $parentPost->getId(), $currentLanguage, $defaultLanguage );
		return new Sidebar(
			$this->getItemByPostId( $displayedItemId, [] ),
			$sections,
			$settings->getHeadingStart(),
			$settings->getHeadingEnd()
		);
	}


	/**
	 * @param int $postId
	 * @param Settings $settings
	 * @param string $currentLanguage
	 * @param string $defaultLanguage
	 * @return Item[]
	 */
	private function getChildItems($postId, $settings, $currentLanguage, $defaultLanguage ) {
		$translatedRootPostId = $this->translationRepository->getTranslatedPostId( $postId, $currentLanguage, $defaultLanguage );
		$childPages = $this->postRepository->getChildPosts( $translatedRootPostId, $settings->getPageOrder() );

		$childItems = [];
		foreach( $childPages as $childPage ) {
			$displayedChildItemId = $this->translationRepository->getTranslatedPostId( $childPage->getId(), $currentLanguage, $defaultLanguage );
			if ( null !== $displayedChildItemId ) {
				$subChildItems = $this->getChildItems( $childPage->getId(), $settings, $currentLanguage, $defaultLanguage );
				$childItems[] = $this->getItemByPostId( (int) $displayedChildItemId, $subChildItems, (bool) $childPage->isMinihome() );
			}
		}
		return $childItems;
	}

	/**
	 * @param int $postId
	 * @param Item[] $childItems
	 * @param bool|null $isMinihome - Only used if it's not null. If provided avoids extra query.
	 * @return Item
	 */
	private function getItemByPostId($postId, array $childItems, $isMinihome = null ) {
		return new Item(
			$postId,
			$this->postRepositoryWPAdapter->getPostTitle( $postId ),
			$this->postRepositoryWPAdapter->getPostPermalink( $postId ),
			$postId === $this->postRepository->getGlobalPost()->getId(),
			null !== $isMinihome ? $isMinihome : $this->postRepositoryWPAdapter->getPostMeta( $postId, '_cms_nav_minihome' ),
			$childItems
		);
	}
}