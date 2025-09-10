<?php
namespace WPML\Nav\Infrastructure\Adapter\WP;

class Hooks extends \WPML\LIB\WP\Hooks {
	/**
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	public static function applyFilters( $name, $value ) {
		return apply_filters( $name, $value );
	}
}
?>