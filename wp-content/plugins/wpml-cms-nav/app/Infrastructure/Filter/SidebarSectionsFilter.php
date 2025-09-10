<?php
namespace WPML\Nav\Infrastructure\Filter;
use \WPML\Nav\Application\Filter\SidebarSectionsFilterInterface;

class SidebarSectionsFilter implements SidebarSectionsFilterInterface {
	public function filter( $value )
	{
		return apply_filters( 'wpml_navbar_sections', $value );
	}
}