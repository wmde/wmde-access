<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;

/**
 * @licence BSD-3-Clause
 */
class BasicAuthFileFetcher implements FileFetcher {

	public function __construct( private string $username, private string $password ) {
	}

	public function fetchFile( string $fileUrl ): string {
		$options = [
			'http' => [
				'method' => 'GET',
				'header' => 'Authorization: Basic ' . base64_encode( $this->username . ':' . $this->password ),
			]
		];

		$context = stream_context_create( $options );
		$fileContent = @file_get_contents( $fileUrl, false, $context );

		if ( is_string( $fileContent ) ) {
			return $fileContent;
		}

		throw new FileFetchingException( $fileUrl );
	}
}