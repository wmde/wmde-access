<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FileFetcher\Cache\Factory;
use FileFetcher\SimpleFileFetcher;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use WMDE\PermissionsOverview\ColumnPresenter;
use WMDE\PermissionsOverview\EnvConfig;
use WMDE\PermissionsOverview\GroupDefinitionBuilder;
use WMDE\PermissionsOverview\HttpPostRequestSender;
use WMDE\PermissionsOverview\SiteConfig;
use WMDE\PermissionsOverview\UserAgentProvidingFileFetcher;
use WMDE\PermissionsOverview\UserDataLoader;
use WMDE\PermissionsOverview\UserMetadataBuilder;
use WMDE\PermissionsOverview\UserPermissionsSite;
use WMDE\PermissionsOverview\WmfLdapGroupDataLoader;
use WMDE\PermissionsOverview\WmfLdapPuppetGroupDataLoader;
use WMDE\PermissionsOverview\WmfPhabricatorGroupDataLoader;

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

$cachingFetcher = ( new Factory() )->newCachingFetcher( new UserAgentProvidingFileFetcher('github.com/wmde/wmde-access'), $psr16Cache, $cacheTtl );

$wmfLdapGroupDataLoader = new WmfLdapGroupDataLoader(
	$cachingFetcher
);
$wmfLdapPuppetGroupDataLoader = new WmfLdapPuppetGroupDataLoader( $cachingFetcher );

$envConfig = Yaml::parseFile( __DIR__ . '/../env.yaml' );

$phabricatorDataLoader = new WmfPhabricatorGroupDataLoader( new HttpPostRequestSender(), $envConfig[EnvConfig::WMF_PHABRICATOR_API_TOKEN] );

$localFileGroupDataLoader = new \WMDE\PermissionsOverview\LocalFileGroupDataLoader( new SimpleFileFetcher() );

$config = Yaml::parseFile( __DIR__ . '/../config.yaml' );
$userConfig = Yaml::parseFile( __DIR__ . '/../users.yaml' );


$siteConfig = new SiteConfig( array_merge( $config, $userConfig ), new GroupDefinitionBuilder(), new UserMetadataBuilder() );

$columnPresenter = new ColumnPresenter( $siteConfig->getColumnDefinitions() );

$userDataLoader = new UserDataLoader(
	$siteConfig->getColumnDefinitions(),
	$siteConfig->getGroupDefinitions(),
	$wmfLdapGroupDataLoader,
	$wmfLdapPuppetGroupDataLoader,
	$phabricatorDataLoader,
	$localFileGroupDataLoader
);

$site = new UserPermissionsSite( $siteConfig, $template, $columnPresenter, $userDataLoader );

echo $site->printHtml();