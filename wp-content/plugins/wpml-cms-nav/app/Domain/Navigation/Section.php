<?php

namespace WPML\Nav\Domain\Navigation;

class Section
{

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var Item[]
	 */
	private $items;

	/**
	 * @param string $title
	 * @param Item[] $items
	 */
	public function __construct( $title, $items ) {
		$this->title = $title;
		$this->items = $items;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return Item[]
	 */
	public function getItems() {
		return $this->items;
	}

}