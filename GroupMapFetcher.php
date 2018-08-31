<?php

namespace WmdeAccess;

use Symfony\Component\Yaml\Yaml;

class GroupMapFetcher {

	private $groupsToCheck;
	private $cache;

	public function __construct( array $groupsToCheck, CachedDoCurl $cache ) {
		$this->groupsToCheck = $groupsToCheck;
		$this->cache = $cache;
	}

	public function getGroupMap() {
		return [
			META_GROUP_LDAP_PUPPET => $this->getLdapPuppetGroups(),
			META_GROUP_LDAP_CLOUD => $this->getLdapMiscGroups( META_GROUP_LDAP_CLOUD ),
			META_GROUP_LDAP_MAGIC => $this->getLdapMiscGroups( META_GROUP_LDAP_MAGIC ),
			META_GROUP_GERRIT => $this->getGerritGroups(),
		];
	}

	private function getGerritGroups() {
		$groupMap = [];
		foreach ( $this->groupsToCheck[META_GROUP_GERRIT] as $groupId => $groupName ) {
			// We can't actually fetch gerrit groups :( so just return an empty array...
			$groupMap[$groupName] = null;
		}
		return $groupMap;
	}

	private function getLdapMiscGroups( $metaGroup ) {
		$groupMap = [];
		foreach ( $this->groupsToCheck[$metaGroup] as $group ) {
			$html = $this->cache->get_data(
				'wmf-ldap-' . $group,
				'https://tools.wmflabs.org/ldap/group/' . $group
			);
			preg_match_all( '/"\/ldap\/user\/([a-zA-Z0-9-]*)"\>/', $html, $userMatches );
			$groupMap[$group] = $userMatches[1];
		}
		return $groupMap;
	}

	private function getLdapPuppetGroups() {
		$opsData = Yaml::parse(
			$this->cache->get_data(
				'wmf-operations-puppet-admin-data',
				'https://raw.githubusercontent.com/wikimedia/puppet/production/modules/admin/data/data.yaml'
			)
		);
		$groupMap = [];
		foreach( $this->groupsToCheck[META_GROUP_LDAP_PUPPET] as $group ) {
			$groupMap[$group] = $opsData['groups'][$group]['members'];
		}
		return $groupMap;
	}

}
