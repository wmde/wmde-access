<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

class GroupMetadata {

	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $label;

	private $url;

	public function __construct( string $name, string $type, string $label, string $url ) {
		$this->name = $name;
		$this->type = $type;
		$this->label = $label;
		$this->url = $url;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function getUrl(): string {
		return $this->url;
	}

}
