<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

/**
 * @licence BSD-3-Clause
 */
class UserDataLoader {

	private $wmfLdapGroupDataLoader;

	public function __construct( WmfLdapGroupDataLoader $wmfLdapGroupDataLoader ) {
		$this->wmfLdapGroupDataLoader = $wmfLdapGroupDataLoader;
	}

	public function loadDataOfUsersFromGroup( string $sourceGroup ): array {
		$userNames = $this->wmfLdapGroupDataLoader->getUsersInGroup( $sourceGroup );

		$userData = [];

		foreach ( $userNames as $user ) {
			$userData[$user] = [];
		}

		return $userData;
	}

}
