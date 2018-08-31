<?php

use WmdeAccess\Cache;
use WmdeAccess\GroupsData;
use WmdeAccess\GroupsPage;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Cache.php';
require_once __DIR__ . '/GroupsData.php';
require_once __DIR__ . '/GroupsPage.php';

$cache = new Cache();
$cache->cache_path = 'cache/';
$cache->cache_time = 60*5;

$groupMap = [];

///////////////////////////////////////////////////////////////////////////
/// Config

const META_GROUP_LDAP_PUPPET = 'ldap-puppet';
const META_GROUP_LDAP_MAGIC = 'ldap-magic';
const META_GROUP_LDAP_CLOUD = 'ldap-cloud-projects';

$metaGroupNames = [
	META_GROUP_LDAP_PUPPET => 'LDAP operations-puppet',
	META_GROUP_LDAP_MAGIC => 'LDAP magic',
	META_GROUP_LDAP_CLOUD => 'Cloud VPS',
];

$groupsToCheck = [
	META_GROUP_LDAP_PUPPET => [
		'deployment',
		'mw-log-readers',
		'researchers',
		'analytics-privatedata-users',
		'analytics-wmde-users',
		'contint-admins',
		'contint-docker',
		'releasers-wikidiff2',
	],
	META_GROUP_LDAP_MAGIC => [
		// ldap groups not in ops puppet
		'wmde',
		'nda',
		'grafana-admin',
	],
	META_GROUP_LDAP_CLOUD => [
		// 'project-bastion', // Not working...
		'project-catgraph',
		'project-deployment-prep',
		'project-lizenzhinweisgenerator',
		'project-mwfileimport',
		// 'project-tools', // Not working....
		'project-wikidata-dev',
		'project-wikidata-query',
		'project-wikidataconcepts',
		'project-wmde-dashboards',
	],
];

///////////////////////////////////////////////////////////////////////////
/// Get group map

$opsData = \Symfony\Component\Yaml\Yaml::parse(
	$cache->get_data(
		'wmf-operations-puppet-admin-data',
		'https://raw.githubusercontent.com/wikimedia/puppet/production/modules/admin/data/data.yaml'
	)
);
foreach( $groupsToCheck[META_GROUP_LDAP_PUPPET] as $group ) {
	$groupMap[META_GROUP_LDAP_PUPPET][$group] = $opsData['groups'][$group]['members'];
}

foreach ( [ META_GROUP_LDAP_MAGIC, META_GROUP_LDAP_CLOUD ] as $metaGroup ) {
	foreach ( $groupsToCheck[$metaGroup] as $group ) {
		$html = $cache->get_data(
			'wmf-ldap-' . $group,
			'https://tools.wmflabs.org/ldap/group/' . $group
		);
		preg_match_all( '/"\/ldap\/user\/([a-zA-Z0-9-]*)"\>/', $html, $userMatches );
		$groupMap[$metaGroup][$group] = $userMatches[1];
	}
}

// TODO github access??

// TODO gerrit groups??

///////////////////////////////////////////////////////////////////////////
/// Output

$data = new GroupsData( $metaGroupNames, $groupMap );

// TODO don't hardcode wmde source group here
echo ( new GroupsPage( $data, META_GROUP_LDAP_MAGIC, 'wmde' ) )->getHtml();
