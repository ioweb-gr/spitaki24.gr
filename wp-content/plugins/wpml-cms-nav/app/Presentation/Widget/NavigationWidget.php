<?php
namespace WPML\Nav\Presentation\Widget;

use WPML\Nav\Presentation\Controller\PageNavigationController;
use function WPML\Container\make;

class NavigationWidget extends \WP_Widget {

	/**
	 * @var PageNavigationController
	 */
	private $pageNavigationController;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'sidebar-navigation', // Base ID.
			__( 'Sidebar Navigation', 'wpml-cms-nav' ), // Name.
			[
				'description' => __( 'Sidebar Navigation', 'wpml-cms-nav' ),
				'classname'   => 'icl_sidebar_navigation',
			] // Args.
		);
	}


	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$args = array_merge(
			[
				'before_widget' => null,
				'after_widget'  => null,
			],
			$args
		);

		$before_widget = $args['before_widget'];
		$after_widget  = $args['after_widget'];

		echo $before_widget;
		echo $this->getPageNavigationController()->renderSidebar();
		echo $after_widget;

	}

	/**
	 * @return PageNavigationController
	 */
	private function getPageNavigationController() {
		if ( null === $this->pageNavigationController ) {
			$this->pageNavigationController = make( PageNavigationController::class );
		}
		return $this->pageNavigationController;
	}
}
