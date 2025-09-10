<?php

namespace WCML\RewriteRules;

use WPML\Core\ISitePress;
use WCML\StandAlone\NullSitePress;
use SitePress;
use WPML\LIB\WP\Hooks as WPHooks;

class ChildMyAccountHooks implements \IWPML_Backend_Action, \IWPML_Frontend_Action, \IWPML_DIC_Action {

	/** @var SitePress|NullSitePress $sitepress */
	private $sitepress;

	/**
	 * @param SitePress|NullSitePress $sitepress
	 */
	public function __construct( ISitePress $sitepress ) {
		$this->sitepress = $sitepress;
	}

	public function add_hooks() {
		WPHooks::onAction( 'init' )
			->then( [ $this, 'addRewriteRules' ] );
	}

	public function addRewriteRules() {
		$myAccountPageId = wc_get_page_id( 'myaccount' );
		if ( $myAccountPageId && get_post_parent( $myAccountPageId ) ) {
			$languages = $this->sitepress->get_active_languages();
			$default   = $this->sitepress->get_default_language();
			foreach ( $languages as $language ) {
				if ( $language['code'] !== $default ) {
					$translatedPageId = $this->sitepress->get_object_id( $myAccountPageId, 'page', true, $language['code'] );
					if ( $translatedPageId ) {
						$translatedPageUri = get_page_uri( $translatedPageId );
						add_rewrite_rule( trailingslashit( $translatedPageUri ) . '?$', 'index.php?pagename=' . $translatedPageUri, 'top' );
						foreach ( WC()->query->query_vars as $endpoint ) {
							$translatedEndpoint = apply_filters( 'wpml_get_endpoint_translation', $endpoint, $endpoint, $language['code'] );
							add_rewrite_rule( trailingslashit( $translatedPageUri ) . $translatedEndpoint . '(/(.*))?/?$', 'index.php?' . $endpoint . '=$matches[2]&pagename=' . $translatedPageUri, 'top' );
						}
					}
				}
			}
		}
	}
}
