<?php
namespace WPML\Nav\Presentation\Controller;

use WPML\LIB\WP\Hooks;
use WPML\Nav\Application\NavigationCache;
use WPML\Nav\Application\PageNaviagtion;
use WPML\Nav\Presentation\Widget\NavigationWidget;

final class PageNavigationController extends AbstractController implements ControllerInterface {

	/** @var NavigationCache  */
	private $navigationCache;

	/** @var PageNaviagtion  */
	private $pageNavigation;

	/**
	 * @param RequestInterface $request
	 * @param NavigationCache $navigationCache
	 * @param PageNaviagtion $pageNavigation
	 */
	public function __construct( RequestInterface $request, NavigationCache $navigationCache, PageNaviagtion $pageNavigation ) {
		parent::__construct( $request );
		$this->navigationCache = $navigationCache;
		$this->pageNavigation = $pageNavigation;
	}

	public function register() {
		Hooks::onAction( 'widgets_init', 10, 0 )
			->then( array( $this, 'registerSidebarWidget' ) );

		Hooks::onAction( 'icl_navigation_sidebar', 10, 0 )
			->then( array( $this, 'renderSidebar' ) );
	}

	public function registerSidebarWidget() {
		register_widget( NavigationWidget::class );
	}

	/**
	 * @return string
	 */
	public function renderSidebar() {
		$post = $this->pageNavigation->getGlobalPost();
		if ( ! $this->request->isPage() || null === $post ) {
			return '';
		}

		$cache_content = $this->navigationCache->getPageCache( $this->request );

		if ( $cache_content ) {
			return $cache_content;
		}

		$navigation = $this->pageNavigation->getSidebar(
			$post,
			$this->request->getCurrentLanguage(),
			$this->request->getDefaultLanguage()
		);

	 	$content = $this->render( 'PageNavigation/Sidebar.html.php', $navigation );

		$this->navigationCache->setPageCache( $this->request, $content );

		return $content;
	}

}