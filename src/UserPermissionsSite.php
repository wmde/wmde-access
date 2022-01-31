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
	 * @var ColumnPresenter
	 */
	private $columnPresenter;
	/**
	 * @var UserDataLoader
	 */
	private $userDataLoader;

	public function __construct(
		SiteConfig $config,
		TemplateWrapper $template,
		ColumnPresenter $columnPresenter,
		UserDataLoader $userDataLoader
	) {
		$this->config = $config;
		$this->template = $template;
		$this->columnPresenter = $columnPresenter;
		$this->userDataLoader = $userDataLoader;
	}

	public function printHtml(): string {
		$userData = $this->userDataLoader->loadDataOfUsersFromGroup( 'wmde' );

		$groups = $this->config->getGroupDefinitions();

		return $this->template->render( [
			'columnMetadata' => $this->columnPresenter->present( $groups ),
			'userData' => $userData,
		] );
	}

}
