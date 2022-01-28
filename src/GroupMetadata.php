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
	private $label;

	private $url;

	public function __construct(string $name, string $label, string $url ) {
		$this->name = $name;
		$this->label = $label;
		$this->url = $url;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function getUrl(): string {
		return $this->url;
	}

}
