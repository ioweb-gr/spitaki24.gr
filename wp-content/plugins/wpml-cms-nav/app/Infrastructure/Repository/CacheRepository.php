<?php

namespace WPML\Nav\Infrastructure\Repository;

use WPML\Nav\Application\Repository\CacheRepositoryInterface;

class CacheRepository implements CacheRepositoryInterface
{
	/** @var \wpdb */
	private $wpdb;

	/**
	 * @param \wpdb $wpdb
	 */
	public function __construct( $wpdb = null ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * @param string $cacheType
	 * @param string $cacheKey
	 * @return string|void|null
	 */
	public function getCacheValue( $cacheType, $cacheKey ) {
		$output_prepared = $this->wpdb->prepare(
			"
                                SELECT data
                                FROM {$this->wpdb->prefix}icl_cms_nav_cache
                                WHERE cache_key=%s
                                AND type='%s'
                                AND DATE_SUB(NOW(), INTERVAL " . WPML_CMS_NAV_CACHE_EXPIRE . ') < timestamp',
			$cacheKey,
			$cacheType
		);
		return $this->wpdb->get_var( $output_prepared );
	}

	/**
	 * @param string $cacheType
	 * @param string $cacheKey
	 * @param string $cacheValue
	 * @return void
	 */
	public function setCacheValue( $cacheType, $cacheKey, $cacheValue ) {
		$this->wpdb->delete( $this->wpdb->prefix . 'icl_cms_nav_cache', [
			'cache_key' => $cacheKey,
			'type' => $cacheType
		]);
		$this->wpdb->insert(
			$this->wpdb->prefix . 'icl_cms_nav_cache',
			array(
				'cache_key' => $cacheKey,
				'type'      => $cacheType,
				'data'      => $cacheValue,
			)
		);
	}

}