<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;

/**
 * @licence BSD-3-Clause
 */
class WmfGerritGroupDataLoader {

	private const BROKEN_JSON_PREFIX = ")]}'";

	public function __construct( private FileFetcher $fileFetcher ) {
	}

	public function getUsersInGroup( string $group ): array {
		$membersUrl = 'https://gerrit.wikimedia.org/r/a/groups/' . $group . '/members/';

		try {
			$membersJson = $this->fileFetcher->fetchFile( $membersUrl );
		} catch ( FileFetchingException $exception ) {
			return [];
		}

		$membersJson = $this->removeGerritBrokenJsonPrefix( $membersJson );

		$membersData = json_decode( $membersJson, true );

		$members = [];
		foreach ( $membersData as $userData ) {
			$members[] = $userData['username'];
		}

		return $members;
	}

	private function removeGerritBrokenJsonPrefix( string $json ) {
		if ( substr( $json, 0, strlen( self::BROKEN_JSON_PREFIX ) ) === self::BROKEN_JSON_PREFIX ) {
			return substr( $json, strlen( self::BROKEN_JSON_PREFIX ) );
		}

		return $json;
	}

}