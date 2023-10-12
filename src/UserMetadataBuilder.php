<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

/**
 * @licence BSD-3-Clause
 */
class UserMetadataBuilder {

	private const KEY_WMF_LDAP = 'wmf-ldap';

	private const KEY_WMF_PHABRICATOR = 'wmf-phabricator';

	public function getUserMetadata( array $usersMetadata ): array {
		$users = [];

		foreach ( $usersMetadata as $canonicalName => $metadata ) {
			$users[$canonicalName] = new User(
				$canonicalName,
				$metadata[self::KEY_WMF_LDAP] ?? '',
				$metadata[self::KEY_WMF_PHABRICATOR] ?? ''
			);
		}

		return $users;
	}

}