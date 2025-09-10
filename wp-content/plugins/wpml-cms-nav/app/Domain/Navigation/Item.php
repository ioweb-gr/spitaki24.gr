<?php

namespace WPML\Nav\Domain\Navigation;

class Item
{

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $permalink;

	/**
	 * @var bool
	 */
	private $isCurrentPost;

	/**
	 * @var bool
	 */
	private $isMinihome;

	/**
	 * @var Item[]
	 */
	private $childItems;

	/**
	 * @param int $id
	 * @param string $title
	 * @param string $permalink
	 * @param bool $isCurrentPost
	 * @param bool $isMinihome
	 * @param Item[] $childItems
	 */
	public function __construct( $id, $title, $permalink, $isCurrentPost, $isMinihome, $childItems ) {
		$this->id = $id;
		$this->title = $title;
		$this->permalink = $permalink;
		$this->isCurrentPost = $isCurrentPost;
		$this->isMinihome = $isMinihome;
		$this->childItems = $childItems;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getPermalink() {
		return $this->permalink;
	}

	/**
	 * @return bool
	 */
	public function isCurrentPost() {
		return $this->isCurrentPost;
	}

	/**
	 * @return bool
	 */
	public function isMinihome() {
		return $this->isMinihome;
	}

	/**
	 * @return Item[]
	 */
	public function getChildItems() {
		return $this->childItems;
	}

}