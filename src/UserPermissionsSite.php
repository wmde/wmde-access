<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

use Twig\TemplateWrapper;

/**
 * @licence BSD-3-Clause
 */
class UserPermissionsSite {

	/**
	 * @var SiteConfig
	 */
	private $config;
	/**
	 * @var TemplateWrapper
	 */
	private $template;
	/**
	 * @var WmfLdapGroupDataLoader
	 */
	private $dataLoader;

	public function __construct(
		SiteConfig $config,
		TemplateWrapper $template,
		WmfLdapGroupDataLoader $dataLoader
	) {
		$this->config = $config;
		$this->template = $template;
		$this->dataLoader = $dataLoader;
	}

	public function printHtml(): string {
		$userData = [];
		$sourceGroup = 'wmde';
		$userNames = $this->dataLoader->getUsersInGroup( $sourceGroup );
		foreach ( $userNames as $user ) {
			$userData[$user] = [];
		}

		$groups = $this->config->getGroupDefinitions();

		$columnDefinitions = $this->config->getColumnDefinitions();

		$columnPresenter = new ColumnPresenter();

		return $this->template->render( [
			'columnMetadata' => $columnPresenter->present( $columnDefinitions, $groups ),
			'userData' => $userData,
		] );
	}

}
