<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

use FileFetcher\FileFetcher;

/**
 * @licence BSD-3-Clause
 */
class LocalFileGroupDataLoader {

	private const LOCAL_FILE_DIR = __DIR__ . '/../data/';

	public function __construct( FileFetcher $fileFetcher ) {
		$this->fileFetcher = $fileFetcher;
	}

	public function getUsersInGroup( string $group ): array {
		// TODO: escape
		$file = self::LOCAL_FILE_DIR . $group;
		$data = $this->fileFetcher->fetchFile( $file );

		$data = trim( $data );

		$users = explode( "\n", $data );

		return array_map( 'trim', $users );
	}

}
