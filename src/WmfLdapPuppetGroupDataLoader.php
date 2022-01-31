<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

use FileFetcher\FileFetcher;
use Symfony\Component\Yaml\Yaml;

/**
 * @licence BSD-3-Clause
 */
class WmfLdapPuppetGroupDataLoader {

	private const GROUP_DATA_FILE_URL = 'https://raw.githubusercontent.com/wikimedia/puppet/production/modules/admin/data/data.yaml';

	/**
	 * @var FileFetcher
	 */
	private $fileFetcher;

	public function __construct( FileFetcher $fileFetcher ) {
		$this->fileFetcher = $fileFetcher;
	}

	public function getUsersInGroup( string $group ): array {
		$dataYaml = $this->fileFetcher->fetchFile( self::GROUP_DATA_FILE_URL );

		$data = Yaml::parse( $dataYaml );

		if ( !array_key_exists( $group, $data['groups'] ) ) {
			return [];
		}

		return $data['groups'][$group]['members'];
	}

}
