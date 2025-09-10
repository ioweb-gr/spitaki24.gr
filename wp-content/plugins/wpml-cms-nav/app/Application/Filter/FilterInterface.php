<?php
namespace WPML\Nav\Application\Filter;

interface FilterInterface {
	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function filter( $value );
}