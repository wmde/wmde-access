<?php

namespace WMDE\PermissionsOverview;

use Twig\TemplateWrapper;

/**
 * @licence BSD-3-Clause
 */
class UserPermissionsSite {

	/**
	 * @var TemplateWrapper
	 */
	private $template;
	/**
	 * @var WmfLdapGroupDataLoader
	 */
	private $dataLoader;

	public function __construct(
		TemplateWrapper $template,
		WmfLdapGroupDataLoader $dataLoader
	) {
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

		return $this->template->render( [
			'columnMetadata' => [],
			'userData' => $userData,
		] );
	}

}
