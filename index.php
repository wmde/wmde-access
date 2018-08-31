<?php

use WmdeAccess\CachedDoCurl;
use WmdeAccess\GroupsData;
use WmdeAccess\GroupsPage;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CachedDoCurl.php';
require_once __DIR__ . '/GroupMapFetcher.php';
require_once __DIR__ . '/GroupsData.php';
require_once __DIR__ . '/GroupsPage.php';

$cache = new CachedDoCurl();
$cache->cache_path = 'cache/';
$cache->cache_time = 60*5;

///////////////////////////////////////////////////////////////////////////
/// Config

const META_GROUP_LDAP_PUPPET = 'ldap-puppet';
const META_GROUP_LDAP_MAGIC = 'ldap-magic';
const META_GROUP_LDAP_CLOUD = 'ldap-cloud-projects';
const META_GROUP_GERRIT = 'gerrit';

$metaGroupNames = [
	META_GROUP_LDAP_MAGIC => 'LDAP magic',
	META_GROUP_LDAP_PUPPET => 'LDAP operations-puppet',
	META_GROUP_LDAP_CLOUD => 'Cloud VPS',
	META_GROUP_GERRIT => 'Gerrit',
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
	META_GROUP_GERRIT => [
		'119' => 'Gerrit Managers'
	],
];

///////////////////////////////////////////////////////////////////////////
/// Output

// TODO don't hardcode wmde source group here
echo (
	new GroupsPage(
		(
			new GroupsData(
			$metaGroupNames,
			(
				new \WmdeAccess\GroupMapFetcher(
					$groupsToCheck,
					$cache
				)
			)->getGroupMap()
		) ),
		META_GROUP_LDAP_MAGIC,
		'wmde'
	)
)->getHtml();
