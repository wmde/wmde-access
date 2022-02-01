<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

/**
 * @licence BSD-3-Clause
 */
class GroupDefinitionBuilder {

	public function getGroups( array $groupData ): array {
		$groups = [];

		foreach ( $groupData as $groupName => $metadata ) {
			$groups[$groupName] = new GroupMetadata(
				$groupName,
				$metadata['type'] ?? '',
				$metadata['label'],
				$metadata['id'] ?? '',
				$metadata['url'] ?? ''
			);
		}

		return $groups;
	}

}
