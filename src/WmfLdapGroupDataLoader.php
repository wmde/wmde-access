<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;

/**
 * @licence BSD-3-Clause
 */
class WmfLdapGroupDataLoader {

	private const WMF_LDAP_GROUP_URL_PREFIX = 'https://ldap.toolforge.org/group/';

	/**
	 * @var FileFetcher
	 */
	private $fileFetcher;

	public function __construct( FileFetcher $fileFetcher ) {
		$this->fileFetcher = $fileFetcher;
	}

	public function getUsersInGroup( string $group ): array {
		$groupDataUrl = self::WMF_LDAP_GROUP_URL_PREFIX . $group;

		try {
			$userHtml = $this->fileFetcher->fetchFile( $groupDataUrl );
		} catch ( FileFetchingException $exception ) {
			return [];
		}

		preg_match_all( '/"\/user\/([a-zA-Z0-9-]*)"\>/', $userHtml, $userMatches );

		return $userMatches[1];
	}

}
