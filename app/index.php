<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FileFetcher\Cache\Factory;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use WMDE\PermissionsOverview\ColumnPresenter;
use WMDE\PermissionsOverview\GroupDefinitionBuilder;
use WMDE\PermissionsOverview\SiteConfig;
use WMDE\PermissionsOverview\UserAgentProvidingFileFetcher;
use WMDE\PermissionsOverview\UserDataLoader;
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
$template = $twig->load( 'index-v2.twig' );

$cacheTtl = 5 * 60;
$psr6Cache = new FilesystemAdapter();
$psr16Cache = new Psr16Cache($psr6Cache);

$wmfLdapGroupDataLoader = new WmfLdapGroupDataLoader(
	( new Factory() )->newCachingFetcher( new UserAgentProvidingFileFetcher( 'github.com/wmde/wmde-access' ), $psr16Cache, $cacheTtl )
);

$config = Yaml::parseFile( __DIR__ . '/../config.yaml' );

$siteConfig = new SiteConfig( $config, new GroupDefinitionBuilder() );

$columnPresenter = new ColumnPresenter( $siteConfig->getColumnDefinitions() );

$userDataLoader = new UserDataLoader( $siteConfig->getColumnDefinitions(), $siteConfig->getGroupDefinitions(), $wmfLdapGroupDataLoader );

$site = new UserPermissionsSite( $siteConfig, $template, $columnPresenter, $userDataLoader );

echo $site->printHtml();
