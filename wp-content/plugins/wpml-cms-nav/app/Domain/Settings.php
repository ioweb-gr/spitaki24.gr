<?php

namespace WPML\Nav\Domain;

class Settings
{

	/**
	 * @var bool
	 */
	private $use_cache;

	/**
	 *
	 * @var string
	 */
	private $page_order;

	/**
	 * Append content at the beggining of heading page navigation.
	 *
	 * @var string|null
	 */
	private $heading_start;

	/**
	 * Append content to the end of heading page navigation.
	 *
	 * @var
	 */
	private $heading_end;

	/**
	 * @param bool $use_cache
	 * @param string $page_order
	 * @param string $heading_start
	 * @param string $heading_end
	 */
	public function __construct(
		$use_cache = true,
		$page_order = 'menu_order',
		$heading_start = '<h4>',
		$heading_end = '</h4>'
	) {
		$this->use_cache = $use_cache;
		$this->page_order = $page_order;
		$this->heading_start = $heading_start;
		$this->heading_end = $heading_end;
	}

	/**
	 * @return bool
	 */
	public function getUseCache() {
		return $this->use_cache;
	}

	/**
	 * @return string
	 */
	public function getPageOrder() {
		return $this->page_order;
	}

	/**
	 * @return string|null
	 */
	public function getHeadingStart() {
		return $this->heading_start;
	}

	/**
	 * @return mixed
	 */
	public function getHeadingEnd() {
		return $this->heading_end;
	}
}