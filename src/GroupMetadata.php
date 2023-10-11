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

	/**
	 * @var string
	 */
	private $id;

	private $url;

	public function __construct( string $name, string $type, string $label, string $id, string $url, private array $extraData ) {
		$this->name = $name;
		$this->type = $type;
		$this->label = $label;
		$this->id = $id;
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

	public function getId(): string {
		return $this->id;
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function getExtraData(): array {
		return $this->extraData;
	}

}
