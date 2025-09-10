<?php
namespace WPML\Nav\Presentation\Controller;

interface RequestInterface {
	/**
	 * @return string
	 */
	public function getCurrentLanguage();

	/**
	 * @return string
	 */
	public function getDefaultLanguage();

	/**
	 * @return string
	 */
	public function getRequestURI();

	/**
	 * @return bool
	 */
	public function isPage();
}