<?php
namespace WPML\Nav\Infrastructure\Adapter\WP;

class PostRepository {

	/**
	 * @param int $postId
	 * @return string
	 */
	public function getPostTitle( $postId ) {
		return get_the_title( $postId );
	}

	/**
	 * @param int $postId
	 * @return string
	 */
	public function getPostPermalink( $postId ) {
		return get_permalink( $postId );
	}

	/**
	 * @param int $postId
	 * @return int[]
	 */
	public function getPostAncestorIds( $postId ) {
		return get_post_ancestors( $postId );
	}

	/**
	 * @param int $postId
	 * @param string $meta_key
	 * @return mixed
	 */
	public function getPostMeta( $postId, $meta_key ) {
		return get_post_meta( $postId, $meta_key, 1 );
	}
}
?>