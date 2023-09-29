<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

/**
 * @licence BSD-3-Clause
 */
class User {

	public function __construct(
		private string $canonicalName,
		private string $wmfLdapUsername,
		private string $wmfPhabricatorUsername
	) {
	}

	public function getCanonicalName() : string {
		return $this->canonicalName;
	}

	public function getWmfLdapUsername(): string {
		return $this->wmfLdapUsername;
	}

	public function getWmfPhabricatorUsername(): string {
		return $this->wmfPhabricatorUsername;
	}

}