<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

/**
 * @licence BSD-3-Clause
 */
class ColumnPresenter {

	/**
	 * @var ColumnDefinitions
	 */
	private $columnDefinitions;

	public function __construct(ColumnDefinitions $columnDefinitions ) {
		$this->columnDefinitions = $columnDefinitions;
	}

	public function present( array $groups ) {
		$presentableData = [];

		$categories = $this->columnDefinitions->getCategories();

		foreach ( $categories as $category ) {
			$presentableData[$category] = [
				'category' => $this->columnDefinitions->getCategoryLabel( $category ),
			];
			$groupsToPresent = $this->columnDefinitions->getGroupsFromCategory( $category );
			foreach ( $groupsToPresent as $group ) {
				$presentableData[$category]['columns'][$group] = $this->presentGroup( $groups[$group] );
			}
		}

		return $presentableData;
	}

	private function presentGroup( GroupMetadata $groupMetadata ): array {
		return [
			'label' => $groupMetadata->getLabel(),
			'url' => $groupMetadata->getUrl(),
		];
	}

}
