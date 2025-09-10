<?php

namespace WPML\Nav\Domain\Navigation;

class Sidebar
{

	/**
	 * @var Item
	 */
	private $rootItem;

	/**
	 * @var Section[]
	 */
	private $sections;

	/**
	 * @var string
	 */
	private $headingContentPrefix = '';

	/**
	 * @var string
	 */
	private $headingContentSuffix = '';

	/**
	 * @param Item $rootItem
	 * @param Section[] $sections
	 * @param string $headingContentPrefix
	 * @param string $headingContentSuffix
	 */
	public function __construct( $rootItem, $sections, $headingContentPrefix, $headingContentSuffix) {
		$this->rootItem = $rootItem;
		$this->sections = $sections;
		$this->headingContentPrefix = $headingContentPrefix;
		$this->headingContentSuffix = $headingContentSuffix;
	}

	/**
	 * @return Item
	 */
	public function getRootItem() {
		return $this->rootItem;
	}

	/**
	 * @return Section[]
	 */
	public function getSections() {
		return $this->sections;
	}

	/**
	 * @return string
	 */
	public function getHeadingContentPrefix() {
		return $this->headingContentPrefix;
	}

	/**
	 * @return string
	 */
	public function getHeadingContentSuffix() {
		return $this->headingContentSuffix;
	}



}