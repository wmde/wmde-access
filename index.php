<?php

use Tlr\Tables\Elements\Rows\BodyRow;
use Tlr\Tables\Elements\Rows\HeaderRow;
use Tlr\Tables\Elements\Table;
use WmdeAccess\Cache;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Cache.php';

$cache = new Cache();
$cache->cache_path = 'cache/';
$cache->cache_time = 60*5;

$groupMap = [];

///////////////////////////////////////////////////////////////////////////
/// Config

$puppetGroups = [
	'deployment',
	'mw-log-readers',
	'researchers',
	'analytics-privatedata-users',
	'analytics-wmde-users',
	'contint-admins',
	'contint-docker',
];

$ldapGroups = [
	// ldap groups not in ops puppet
	'wmde',
	'nda',
	'grafana-admin',
	// cloud projects
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
];

///////////////////////////////////////////////////////////////////////////
/// Get group map

$opsData = \Symfony\Component\Yaml\Yaml::parse(
	$cache->get_data(
		'wmf-operations-puppet-admin-data',
		'https://raw.githubusercontent.com/wikimedia/puppet/production/modules/admin/data/data.yaml'
	)
);
foreach( $puppetGroups as $group ) {
	$groupMap[$group] = $opsData['groups'][$group]['members'];
}

foreach ( $ldapGroups as $group ) {
	$html = $cache->get_data(
		'wmf-ldap-' . $group,
		'https://tools.wmflabs.org/ldap/group/' . $group
	);
	preg_match_all( '/"\/ldap\/user\/([a-zA-Z0-9-]*)"\>/', $html, $userMatches );
	$groupMap[$group] = $userMatches[1];
}

// TODO github access??

// TODO gerrit groups??

///////////////////////////////////////////////////////////////////////////
/// Create user map

$userMap = [];

foreach ( $groupMap['wmde'] as $wmdeUser ) {
	foreach ( $groupMap as $group => $groupUsers ) {
		if ( in_array( $wmdeUser, $groupUsers ) ) {
			$userMap[$wmdeUser][] = $group;
		}
	}
}

///////////////////////////////////////////////////////////////////////////
/// Create some tables?

$userHtmlGen = function ( $user ) {
	return '<a href="https://tools.wmflabs.org/ldap/user/' . $user . '">' . $user . '<a/>';
};

$opsLdapGroupHtmlGen = function ( $group ) {
	return $group;
};

$ldapGroupHtmlGen = function ( $group ) {
	$cloudVpsLinkHtmlGen = function ( $project ) {
		return '<a href="https://tools.wmflabs.org/openstack-browser/project/' . $project . '">' . $project . '</a>';
	};

	// If this is a cloud VPS project
	if ( substr( $group, 0, 8 ) === 'project-' ) {
		return $cloudVpsLinkHtmlGen( str_replace( 'project-', '', $group ) );
	}
	return $group;
};

$_numOfCloudVpsProjects = function ( $projects ) {
	$counter = 0;
	foreach ( $projects as $project ) {
		if ( substr( $project, 0, 8 ) === 'project-' ) {
			$counter++;
		}
	}
	return $counter;
};

$table = new Table();
$table->class('table table-striped table-bordered table-hover table-sm');

/** @var HeaderRow $headerRow */
$headerRow = $table->header()->row();
$headerRow->cell( '' ); // first cell...
$headerRow->cell( 'LDAP operations-puppet' )->spanColumns( count( $puppetGroups ) );
$headerRow->cell( 'LDAP magic' )->spanColumns( count( $ldapGroups ) -
	$_numOfCloudVpsProjects( $ldapGroups ) );
$headerRow->cell( 'Cloud VPS' )->spanColumns( $_numOfCloudVpsProjects( $ldapGroups ) );

$headerRow = $table->header()->row();
$headerRow->cell( '' ); // first cell...
foreach ( $puppetGroups as $group ) {
	$headerRow->cell( $opsLdapGroupHtmlGen($group) )->raw();
}
foreach ( $ldapGroups as $group ) {
	$headerRow->cell( $ldapGroupHtmlGen( $group ) )->raw();
}

foreach ( $userMap as $user => $userGroups ) {
	/** @var BodyRow $userRow */
	$userRow = $table->body()->row();
	$userRow->cell( $userHtmlGen( $user ) )->raw();
	foreach ( $puppetGroups as $group ) {
		if ( in_array( $group, $userGroups ) ) {
			$userRow->cell( 'Yes' );
		} else {
			$userRow->cell( '' );
		}
	}
	foreach ( $ldapGroups as $group ) {
		if ( in_array( $group, $userGroups ) ) {
			$userRow->cell( 'Yes' );
		} else {
			$userRow->cell( '' );
		}
	}
}

///////////////////////////////////////////////////////////////////////////
/// Ouput

echo "<html>";
echo "<head>";
echo "<link rel=\"stylesheet\" href=\"https://tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/4.0.0-beta/css/bootstrap.min.css\">";
echo "</head>";
echo "<body>";
echo "<h1>WMDE groups</h1>";
echo "<p>Code for this tool can be found @ <a href='https://github.com/addshore/wmde-access' >https://github.com/addshore/wmde-access</a></p>";
echo $table->render();
echo "</body>";
echo "</html>";
