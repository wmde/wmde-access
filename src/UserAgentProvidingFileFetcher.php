<?php

namespace WMDE\PermissionsOverview;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;

class UserAgentProvidingFileFetcher implements FileFetcher {

	/**
	 * @var string
	 */
	private $userAgent;

	public function __construct(string $userAgent ) {
		$this->userAgent = $userAgent;
	}

	public function fetchFile( string $fileUrl ): string {
		$options = [
			'http' => [
				'method' => 'GET',
				'header' => "User-Agent: github.com/wmde/wmde-access\r\n"
			],
		];

		$context = stream_context_create( $options );
		$fileContent = @file_get_contents( $fileUrl, false, $context );

		if ( is_string( $fileContent ) ) {
			return $fileContent;
		}

		throw new FileFetchingException( $fileUrl );
	}

}
