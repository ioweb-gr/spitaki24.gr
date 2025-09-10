<?php
namespace WPML\Nav\Application\Filter;

interface SidebarSectionsFilterInterface extends FilterInterface {

	/**
	 * @param array<string, int[]> $value
	 * @return array<string, int[]>
	 */
	public function filter( $value );
}