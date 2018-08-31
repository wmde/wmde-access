<?php

namespace WmdeAccess;

use Symfony\Component\Yaml\Yaml;

class GroupMapFetcher {

	private $groupsToCheck;
	private $cache;

	public function __construct( array $groupsToCheck, Cache $cache ) {
		$this->groupsToCheck = $groupsToCheck;
		$this->cache = $cache;
	}

	public function getGroupMap() {
		$groupMap = [];

		$opsData = Yaml::parse(
			$this->cache->get_data(
				'wmf-operations-puppet-admin-data',
				'https://raw.githubusercontent.com/wikimedia/puppet/production/modules/admin/data/data.yaml'
			)
		);
		foreach( $this->groupsToCheck[META_GROUP_LDAP_PUPPET] as $group ) {
			$groupMap[META_GROUP_LDAP_PUPPET][$group] = $opsData['groups'][$group]['members'];
		}

		foreach ( [ META_GROUP_LDAP_MAGIC, META_GROUP_LDAP_CLOUD ] as $metaGroup ) {
			foreach ( $this->groupsToCheck[$metaGroup] as $group ) {
				$html = $this->cache->get_data(
					'wmf-ldap-' . $group,
					'https://tools.wmflabs.org/ldap/group/' . $group
				);
				preg_match_all( '/"\/ldap\/user\/([a-zA-Z0-9-]*)"\>/', $html, $userMatches );
				$groupMap[$metaGroup][$group] = $userMatches[1];
			}
		}

		return $groupMap;
	}

}
