<?php

use Symfony\Component\Yaml\Yaml;
use WmdeAccess\CachedDoCurl;
use WmdeAccess\GroupsData;
use WmdeAccess\GroupsPage;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CachedDoCurl.php';
require_once __DIR__ . '/GroupsData.php';
require_once __DIR__ . '/GroupsPage.php';

$cachedRequests = new CachedDoCurl();
$cachedRequests->cache_path = 'cache/';
$cachedRequests->cache_time = 60*5;

///////////////////////////////////////////////////////////////////////////
/// Config

const MG_LDAP_PUPPET = 'ldap-puppet';
const MG_LDAP_MAGIC = 'ldap-magic';
const MG_LDAP_CLOUD = 'ldap-cloud-projects';
const MG_GERRIT = 'gerrit';
const MG_PHABRICATOR = 'phabricator';

$groupsToCheck = [
	MG_LDAP_PUPPET => [
		'deployment',
		'wdqs-admins',
		'analytics-privatedata-users',
		'analytics-wmde-users',
		'contint-admins',
		'contint-docker',
		'releasers-wikibase',
		'releasers-wikidiff2',
	],
	MG_LDAP_MAGIC => [
		// ldap groups not in ops puppet
		'wmde',
		'nda',
	],
	MG_LDAP_CLOUD => [
		// 'project-bastion', // Not working...
		'project-deployment-prep',
		'project-lizenzhinweisgenerator',
		// 'project-tools', // Not working....
		'project-wikidata-dev',
		'project-wikidata-query',
		'project-wmde-dashboards',
	],
	MG_GERRIT => [
		'119' => 'Gerrit Managers'
	],
	MG_PHABRICATOR => [
		'1776' => 'Project-Admins',
		'61' => 'WMF-NDA',
		'30' => 'Security',
	],
];

$ldapMagicFetcherGenerator = function ( $metaGroup ) use ( $groupsToCheck, $cachedRequests ) {
	return function () use ( $groupsToCheck, $metaGroup, $cachedRequests ) {
		$groupMap = [];
		foreach ( $groupsToCheck[$metaGroup] as $group ) {
			$html = $cachedRequests->get_data(
				'wmf-ldap-' . $group,
				'https://ldap.toolforge.org/group/' . $group
			);
			preg_match_all( '/"\/user\/([a-zA-Z0-9-]*)"\>/', $html, $userMatches );
			$groupMap[$group] = $userMatches[1];
		}
		return $groupMap;
	};
};
$metaGroupFetchers = null;

///////////////////////////////////////////////////////////////////////////
/// Output

$templateLoader = new \Twig\Loader\FilesystemLoader( 'templates' );
$twig = new \Twig\Environment(
	$templateLoader,
	[
		'auto_reload' => true,
		'cache' => 'cache',
	]
);
$template = $twig->load( 'index.twig' );

// TODO don't hardcode wmde source group here
echo (
	new GroupsPage(
		$template,
		(
			new GroupsData(
				[
					MG_LDAP_MAGIC => 'LDAP magic',
					MG_LDAP_PUPPET => 'LDAP operations-puppet',
					MG_LDAP_CLOUD => 'Cloud VPS',
					MG_GERRIT => 'Gerrit',
					MG_PHABRICATOR => 'Phabricator',
				],
				[
					MG_LDAP_MAGIC => ( $ldapMagicFetcherGenerator( MG_LDAP_MAGIC ) )(),
					MG_LDAP_PUPPET => ( function() use ( $cachedRequests, $groupsToCheck ) {
						$opsData = Yaml::parse(
							$cachedRequests->get_data(
								'wmf-operations-puppet-admin-data',
								'https://raw.githubusercontent.com/wikimedia/puppet/production/modules/admin/data/data.yaml'
							)
						);
						$groupMap = [];
						foreach( $groupsToCheck[MG_LDAP_PUPPET] as $group ) {
							$groupMap[$group] = $opsData['groups'][$group]['members'];
						}
						return $groupMap;
					} )(),
					MG_LDAP_CLOUD => ( $ldapMagicFetcherGenerator( MG_LDAP_CLOUD ) )(),
					MG_GERRIT => ( function() use ( $groupsToCheck ) {
						$groupMap = [];
						foreach ( $groupsToCheck[MG_GERRIT] as $groupName ) {
							$file = __DIR__ . '/data/gerrit_' . $groupName;
							$data = file_get_contents( $file );
							$users = explode( "\n", trim( $data ) );
							$groupMap[$groupName] = array_map( 'trim', $users );
						}
						return $groupMap;
					} )(),
					MG_PHABRICATOR => ( function() use ( $groupsToCheck ) {
						$groupMap = [];
						foreach ( $groupsToCheck[MG_PHABRICATOR] as $groupName ) {
							$file = __DIR__ . '/data/phabricator_' . $groupName;
							$data = file_get_contents( $file );
							$users = explode( "\n", trim( $data ) );
							$groupMap[$groupName] = array_map( 'trim', $users );
						}
						return $groupMap;
					} )(),
				]
			)
		),
		MG_LDAP_MAGIC,
		'wmde'
	)
)->getHtml();
