<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FileFetcher\Cache\Factory;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use WMDE\PermissionsOverview\UserAgentProvidingFileFetcher;
use WMDE\PermissionsOverview\UserPermissionsSite;
use WMDE\PermissionsOverview\WmfLdapGroupDataLoader;

$templateLoader = new FilesystemLoader( __DIR__ . '/../templates' );
$twig = new Environment(
	$templateLoader,
	[
		'auto_reload' => true,
		'cache' => 'cache',
	]
);
$template = $twig->load( 'index.twig' );

$cacheTtl = 5 * 60;
$psr6Cache = new FilesystemAdapter();
$psr16Cache = new Psr16Cache($psr6Cache);

$dataLoader = new WmfLdapGroupDataLoader(
	( new Factory() )->newCachingFetcher( new UserAgentProvidingFileFetcher( 'github.com/wmde/wmde-access' ), $psr16Cache, $cacheTtl )
);

$site = new UserPermissionsSite( $template, $dataLoader );

echo $site->printHtml();
