<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

class ColumnPresenter {

	public function present( ColumnDefinitions $columns, array $groups ) {
		$presentableData = [];

		$categories = $columns->getCategories();

		foreach ( $categories as $category ) {
			$presentableData[$category] = [
				'category' => $columns->getCategoryLabel( $category ),
			];
			$groupsToPresent = $columns->getGroupsFromCategory( $category );
			foreach ( $groupsToPresent as $group ) {
				$presentableData[$category]['columns'][] = $this->presentGroup( $groups[$group] );
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
