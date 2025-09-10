<?php
/*
Plugin Name: Preserve Page and Taxonomy Hierarchy on Edit Menus Screen
Version: 0.1
Plugin URI: https://core.trac.wordpress.org/ticket/18282
Description: Disables paging for hierarchical post types and taxonomies on Edit Menus screen to preserve proper hierarchy in meta boxes.
Author: Sergey Biryukov
Author URI: http://profiles.wordpress.org/sergeybiryukov/
*/

class Preserve_Page_and_Taxonomy_Hierarchy {

	function __construct() {
		add_action( 'load-nav-menus.php', array( $this, 'init' ) );
	}

	function init() {
		add_action( 'pre_get_posts',    array( $this, 'disable_paging_for_hierarchical_post_types' ) );
		add_filter( 'get_terms_args',   array( $this, 'remove_limit_for_hierarchical_taxonomies' ), 10, 2 );
		add_filter( 'get_terms_fields', array( $this, 'remove_page_links_for_hierarchical_taxonomies' ), 10, 3 );
	}

	function disable_paging_for_hierarchical_post_types( $query ) {
		if ( ! is_admin() || 'nav-menus' !== get_current_screen()->id ) {
			return;
		}

		if ( ! is_post_type_hierarchical( $query->get( 'post_type' ) ) ) {
			return;
		}

		if ( 50 == $query->get( 'posts_per_page' ) ) {
			$query->set( 'nopaging', true );
		}
	}

	function remove_limit_for_hierarchical_taxonomies( $args, $taxonomies ) {
		if ( ! is_admin() || 'nav-menus' !== get_current_screen()->id ) {
			return $args;
		}

		if ( ! is_taxonomy_hierarchical( reset( $taxonomies ) ) ) {
			return $args;
		}

		if ( 50 == $args['number'] ) {
			$args['number'] = '';
		}

		return $args;
	}

	function remove_page_links_for_hierarchical_taxonomies( $selects, $args, $taxonomies ) {
		if ( ! is_admin() || 'nav-menus' !== get_current_screen()->id ) {
			return $selects;
		}

		if ( ! is_taxonomy_hierarchical( reset( $taxonomies ) ) ) {
			return $selects;
		}

		if ( 'count' === $args['fields'] ) {
			$selects = array( '1' );
		}

		return $selects;
	}

}

new Preserve_Page_and_Taxonomy_Hierarchy;
?>