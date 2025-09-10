<?php

namespace WPML\Nav\Domain;

class Post
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int|null
	 */
	private $parent_id;

	/**
	 * @var int[]|null
	 */
	private $ancestors;

	/**
	 * Represents whether this post should be the highest ancestor displayed
	 * in a hierarchical navigation where it is shown.
	 *
	 * @var bool|null
	 */
	private $is_minihome;

	/**
	 * @var string|null
	 */
	private $section;

	/**
	 * @param int $id
	 * @param int|null $parent_id
	 * @param int[]|null $ancestors
	 * @param bool|null $is_minihome
	 * @param string|null $section
	 */
	public function __construct(
		$id,
		$parent_id,
		$ancestors = null,
		$is_minihome = null,
		$section = null
	) {
		$this->id = $id;
		$this->parent_id = $parent_id;
		$this->ancestors = $ancestors;
		$this->is_minihome = $is_minihome;
		$this->section = $section;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return bool
	 */
	public function hasAncestors() {
		return isset( $this->ancestors ) && is_array( $this->ancestors ) && count( $this->ancestors ) > 0;
	}

	/**
	 * @return int[]|null
	 */
	public function getAncestors() {
		return $this->ancestors;
	}

	/**
	 * @return bool|null
	 */
	public function isMinihome() {
		return $this->is_minihome;
	}

	/**
	 * @return string|null
	 */
	public function getSection() {
		return $this->section;
	}

	/**
	 * @return int|null
	 */
	public function getParentId() {
		return $this->parent_id;
	}

}