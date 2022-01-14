<?php

namespace WmdeAccess;

use Twig\TemplateWrapper;

class GroupsPage {

	private $data;
	private $sourceMetaGroup;
	private $sourceGroup;

	/**
	 * @var TemplateWrapper
	 */
	private $template;

	public function __construct(
		TemplateWrapper $template,
		GroupsData $data,
		$sourceMetaGroup,
		$sourceGroup ) {
		$this->template = $template;
		$this->data = $data;
		$this->sourceMetaGroup = $sourceMetaGroup;
		$this->sourceGroup = $sourceGroup;
	}

	public function getHtml() {
		$data = $this->data;
		$users = $data->getUsersInGroup( $this->sourceMetaGroup, $this->sourceGroup ); // MG_LDAP_MAGIC, 'wmde'
		$userData = [];
		// TODO: this is quite terrible
		foreach ( $users as $username ) {
			foreach ( $data->getMetaGroupKeys() as $category ) {
				foreach ( $data->getGroupsInMetaGroup( $category ) as $group ) {
					$userInGroup = $this->data->userIsInGroup( $username, $category, $group );
					if ( $userInGroup === true ) {
						$userData[$username][$category][$group] = 'yes';
					} elseif ( $userInGroup === false ) {
						$userData[$username][$category][$group] = 'no';
					} else {
						$userData[$username][$category][$group] = 'unknown';
					}
				}
			}
		}
		return $this->template->render( [
			'columnMetadata' => array_map(
				function ( $category ) use ( $data ) {
					return [
						'category' => $data->getMetaGroupText( $category ),
						'columnCount' => $data->getNumberOfGroupsInMetaGroup( $category ),
						'columns' => array_map(
							// TODO: this is not very nice
							function ( $column ) use ( $category ) {
								$columnName = $column;
								if ( $category === 'ldap-cloud-projects' ) {
									$columnName = str_replace( 'project-', '', $columnName );
								}
								$columnUrl = '';
								if ( $category === 'ldap-cloud-projects' ) {
									$columnUrl = 'https://openstack-browser.toolforge.org/project/' . $columnName;
								}
								if ( $category === 'gerrit' && $columnName === 'Gerrit Managers' ) {
									$columnUrl = 'https://gerrit.wikimedia.org/r/#/admin/groups/119,members';
								}
								if ( $category === 'phabricator' ) {
									if ( $columnName === 'WMF-NDA' ) {
										$columnName = 'NDA';
										$columnUrl = 'https://phabricator.wikimedia.org/project/members/61/';
									} elseif ( $columnName === 'Security' ) {
										$columnUrl = '/https://phabricator.wikimedia.org/project/members/30/';
									} elseif ( $columnName === 'Project-Admins' ) {
										$columnUrl = 'https://phabricator.wikimedia.org/project/members/1776/';
									}
								}
								return [
									'name' => $columnName,
									'url' => $columnUrl,
								];
							},
							$data->getGroupsInMetaGroup( $category ), // TODO: column names can be formatted (URL, name change for display)
						),
					];
				},
				$this->data->getMetaGroupKeys()
			),
			'userData' => $userData,
		] );
	}

}
