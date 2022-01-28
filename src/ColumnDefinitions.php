<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

/**
 * @licence BSD-3-Clause
 */
class ColumnDefinitions {

	private $columnData;

	public function __construct( array $columnData ) {
		$this->columnData = $columnData;
	}

	public function getCategories(): array {
		return array_keys( $this->columnData );
	}

	public function getCategoryLabel( string $category ): string {
		return $this->columnData[$category]['category'];
	}

	public function getGroupsFromCategory( string $category ): array {
		return $this->columnData[$category]['columns'];
	}

}
