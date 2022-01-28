<?php

declare( strict_types = 1 );

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

		$groupData = [
			'ldap-wmde' => [ 'label' => 'wmde' ],
			'ldap-nda' => [ 'label' => 'nda' ],
			'deployment' => [ 'label' => 'deployment' ],
			'wdqs-admins' => [ 'label' => 'wdqs-admins' ],
			'analytics-privatedata-users' => [ 'label' => 'analytics-privatedata-users' ],
			'analytics-wmde-users' => [ 'label' => 'analytics-wmde-users' ],
			'contint-admins' => [ 'label' => 'contint-admins' ],
			'contint-docker' => [ 'label' => 'contint-docker' ],
			'releasers-wikibase' => [ 'label' => 'releasers-wikibase' ],
			'releasers-wikidiff2' => [ 'label' => 'releasers-wikidiff2' ],
			'project-deployment-prep' => [ 'label' => 'deployment-prep' ],
			'project-lizenzhinweisgenerator' => [ 'label' => 'lizenzhinweisgenerator' ],
			'project-wikidata-dev' => [ 'label' => 'wikidata-dev' ],
			'project-wikidata-query' => [ 'label' => 'wikidata-query' ],
			'project-wmde-dashboards' => [ 'label' => 'wmde-dashboards' ],
			'gerrit-managers' => [
				'label' => 'Gerrit Managers',
				'url' => 'https://gerrit.wikimedia.org/r/#/admin/groups/119,members',
			],
			'phabricator-project-admins' => [
				'label' => 'Project-Admins',
				'url' => 'https://phabricator.wikimedia.org/project/members/1776/',
			],
			'phabricator-wmf-nda' => [
				'label' => 'NDA',
				'url' => 'https://phabricator.wikimedia.org/project/members/61/',
			],
			'phabricator-security' => [
				'label' => 'Security',
				'url' => 'https://phabricator.wikimedia.org/project/members/30/',
			],
		];

		$groups = ( new GroupDefinitionBuilder() )->getGroups( $groupData );

		$columnData = [
			'ldap-magic' => [
				'category' => 'LDAP magic',
				'columns' => [
					'ldap-wmde',
					'ldap-nda',
				],
			],
			'ldap-puppet' => [
				'category' => 'LDAP operations-puppet',
				'columns' => [
					'deployment',
					'wdqs-admins',
					'analytics-privatedata-users',
					'analytics-wmde-users',
					'contint-admins',
					'contint-docker',
					'releasers-wikibase',
					'releasers-wikidiff2',
				],
			],
			'ldap-cloud-projects' => [
				'category' => 'Cloud VPS',
				'columns' => [
					'project-deployment-prep',
					'project-lizenzhinweisgenerator',
					'project-wikidata-dev',
					'project-wikidata-query',
					'project-wmde-dashboards',
				],
			],
			'gerrit' => [
				'category' => 'Gerrit',
				'columns' => [
					'gerrit-managers',
				],
			],
			'phabricator' => [
				'category' => 'Phabricator',
				'columns' => [
					'phabricator-project-admins',
					'phabricator-wmf-nda',
					'phabricator-security',
				],
			],
		];

		$columnDefinitions = new ColumnDefinitions( $columnData );

		$columnPresenter = new ColumnPresenter();

		return $this->template->render( [
			'columnMetadata' => $columnPresenter->present( $columnDefinitions, $groups ),
			'userData' => $userData,
		] );
	}

}
