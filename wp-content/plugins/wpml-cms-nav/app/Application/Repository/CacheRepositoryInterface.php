<?php
namespace WPML\Nav\Application\Repository;

interface CacheRepositoryInterface {

	/**
	 * @param string $cacheType
	 * @param string $cacheKey
	 * @return string|null
	 */
	public function getCacheValue( $cacheType, $cacheKey );

	/**
	 * @param string $cacheType
	 * @param string $cacheKey
	 * @param string $cacheValue
	 * @return void
	 */
	public function setCacheValue( $cacheType, $cacheKey, $cacheValue );
}
