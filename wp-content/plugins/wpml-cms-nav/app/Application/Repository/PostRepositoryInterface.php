<?php
namespace WPML\Nav\Application\Repository;

use WPML\Nav\Domain\Post;

interface PostRepositoryInterface {
	/**
	 * @return Post|null
	 */
	public function getGlobalPost();

	/**
	 * @param Post $post
	 * @return Post|null
	 */
	public function getHighestAncestorOrMinihome( $post );

	/**
	 * @param int $rootPostId
	 * @param string $page_order
	 * @param string $post_type
	 * @return Post[]
	 */
	public function getChildPosts( $rootPostId, $page_order, $post_type = 'page' );
}
