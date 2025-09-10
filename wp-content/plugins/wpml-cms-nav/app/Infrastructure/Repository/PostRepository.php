<?php

namespace WPML\Nav\Infrastructure\Repository;

use WPML\Nav\Application\Repository\PostRepositoryInterface;
use WPML\Nav\Domain\Post;
use WPML\Nav\Infrastructure\Adapter\WP\PostRepository as PostRepositoryWPAdapter;

class PostRepository implements PostRepositoryInterface
{
	/** @var \wpdb */
	private $wpdb;

	/** @var PostRepositoryWPAdapter  */
	private $postRepositoryWPAdapter;

	/**
	 * @param \wpdb $wpdb
	 * @param PostRepositoryWPAdapter $postRepositoryWPAdapter
	 */
	public function __construct(
		\wpdb $wpdb,
		PostRepositoryWPAdapter $postRepositoryWPAdapter
	) {
		$this->wpdb = $wpdb;
		$this->postRepositoryWPAdapter = $postRepositoryWPAdapter;
	}

	/**
	 * @inheritDoc
	 */
	public function getGlobalPost() {
		global $post;
		if ( null === $post ) {
			return null;
		}
		$ancestors = isset( $post->ancestors) ?
			$post->ancestors :
			$this->postRepositoryWPAdapter->getPostAncestorIds( $post->ID );
		return new Post(
			$post->ID,
			$post->post_parent,
			$ancestors,
			!! get_post_meta( $post->ID, '_cms_nav_minihome', true )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getHighestAncestorOrMinihome( $post ) {
		$ancestorIds = $post->getAncestors();
		$parentId = isset( $ancestorIds[ 0 ] ) ? $ancestorIds[ 0 ] : 0;
		$rootPostId = null;
		do {
			$ancestor_query = $this->wpdb->prepare(
				"
SELECT p1.ID, p1.post_parent, p2.meta_value, (p2.meta_value IS NOT NULL && p2.meta_value <> '') AS minihome
FROM {$this->wpdb->posts} p1
LEFT JOIN {$this->wpdb->postmeta} p2 ON p1.ID=p2.post_id AND (meta_key='_cms_nav_minihome' OR meta_key IS NULL)
WHERE post_type='page' AND p1.ID=%d",
				$parentId
			);
			$ancestor_post = $this->wpdb->get_row( $ancestor_query );
			$rootPostId      = $ancestor_post->ID;
			$parentId        = $ancestor_post->post_parent;
			$minihome        = $ancestor_post->minihome;
		} while ( $parentId != 0 && ! $minihome );

		if ( null === $rootPostId ) {
			return null;
		}

		return new Post(
			(int) $ancestor_post->ID,
			(int) $parentId,
			[],
			(bool) $ancestor_post->minihome
		);
	}

	/**
	 * @param $rootPostId
	 * @param $page_order
	 * @param $post_type
	 * @return Post[]
	 */
	public function getChildPosts( $rootPostId, $page_order, $post_type = 'page' ) {
		$sub_prepared = $this->wpdb->prepare(
			"
                    SELECT p1.ID, p2.meta_value AS section, p3.meta_value AS minihome FROM {$this->wpdb->posts} p1
                    LEFT JOIN {$this->wpdb->postmeta} p2 ON p1.ID=p2.post_id AND (p2.meta_key='_cms_nav_section' OR p2.meta_key IS NULL)
                    LEFT JOIN {$this->wpdb->postmeta} p3 ON p1.ID=p3.post_id AND (p3.meta_key='_cms_nav_minihome' OR p3.meta_key IS NULL)
                    WHERE post_parent=%d AND post_type='{$post_type}' AND post_status='publish' ORDER BY " . $page_order,
			$rootPostId
		);
		$results = $this->wpdb->get_results( $sub_prepared );
		$childPosts = [];
		foreach( $results as $result ) {
			$childPosts[] = new Post( (int) $result->ID, $rootPostId, null, (bool) $result->minihome, $result->section );
		}
		return $childPosts;
	}

}