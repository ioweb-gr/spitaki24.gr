<?php
namespace WPML\Nav\Infrastructure\Adapter;

use WPML\Element\API\Languages;
use WPML\Nav\Presentation\Controller\RequestInterface;

class Request implements RequestInterface {
	public function getCurrentLanguage() {
		return Languages::getCurrentCode();
	}

	public function getDefaultLanguage() {
		return Languages::getDefaultCode();;
	}

	public function getRequestURI() {
		return isset( $_SERVER[ 'REQUEST_URI' ] ) ? $_SERVER[ 'REQUEST_URI' ] : '';
	}

	public function isPage() {
		return is_page();
	}
}

?>