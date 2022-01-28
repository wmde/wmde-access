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

		$columns = [
			'ldap-magic' => [
				'category' => 'LDAP magic',
				'columnCount' => 2,
				'columns' => [
					'ldap-wmde' => [ 'label' => 'wmde' ],
					'ldap-nda' => [ 'label' => 'nda' ],
				],
			],
			'ldap-puppet' => [
				'category' => 'LDAP operations-puppet',
				'columnCount' => 8,
				'columns' => [
					[ 'label' => 'deployment' ],
					[ 'label' => 'wdqs-admins' ],
					[ 'label' => 'analytics-privatedata-users' ],
					[ 'label' => 'analytics-wmde-users' ],
					[ 'label' => 'contint-admins' ],
					[ 'label' => 'contint-docker' ],
					[ 'label' => 'releasers-wikibase' ],
					[ 'label' => 'releasers-wikidiff2' ],
				],
			],
			'ldap-cloud-projects' => [
				'category' => 'Cloud VPS',
				'columnCount' => 5,
				'columns' => [
					[ 'label' => 'deployment-prep' ],
					[ 'label' => 'lizenzhinweisgenerator' ],
					[ 'label' => 'wikidata-dev' ],
					[ 'label' => 'wikidata-query' ],
					[ 'label' => 'wmde-dashboards' ],
				],
			],
			'gerrit' => [
				'category' => 'Gerrit',
				'columnCount' => 1,
				'columns' => [
					[
						'label' => 'Gerrit Managers',
						'url' => 'https://gerrit.wikimedia.org/r/#/admin/groups/119,members',
					],
				],
			],
			'phabricator' => [
				'category' => 'Phabricator',
				'columnCount' => 3,
				'columns' => [
					[
						'label' => 'Project-Admins',
						'url' => 'https://phabricator.wikimedia.org/project/members/1776/',
					],
					[
						'label' => 'NDA',
						'url' => 'https://phabricator.wikimedia.org/project/members/61/',
					],
					[
						'label' => 'Security',
						'url' => 'https://phabricator.wikimedia.org/project/members/30/',
					],
				],
			],
		];

		return $this->template->render( [
			'columnMetadata' => $columns,
			'userData' => $userData,
		] );
	}

}
