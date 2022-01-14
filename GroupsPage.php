<?php

namespace WmdeAccess;

use Tlr\Tables\Elements\Rows\BodyRow;
use Tlr\Tables\Elements\Rows\HeaderRow;
use Tlr\Tables\Elements\Table;
use Twig\TemplateWrapper;

class GroupsPage {

	private $data;
	private $metaGroupFormatters;
	private $sourceMetaGroup;
	private $sourceGroup;

	/**
	 * @var TemplateWrapper
	 */
	private $template;

	public function __construct(
		TemplateWrapper $template,
		GroupsData $data,
		$metaGroupFormatters,
		$sourceMetaGroup,
		$sourceGroup ) {
		$this->template = $template;
		$this->data = $data;
		$this->metaGroupFormatters = $metaGroupFormatters;
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

	private function getTable() {
		$table = new Table();
		$table->class('table table-striped table-bordered table-hover table-sm');

		$metaGroupKeys = $this->data->getMetaGroupKeys();
		/** @var HeaderRow $headerRow */

		// Create the meta groups header
		$headerRow = $table->header()->row();
		$headerRow->cell( '' ); // first cell...
		foreach ( $metaGroupKeys as $metaKey ) {
			$headerRow->cell( $this->data->getMetaGroupText( $metaKey ) )
				->spanColumns( $this->data->getNumberOfGroupsInMetaGroup( $metaKey ) )
				->class( 'group-type' );
		}

		// Create the main groups header
		$headerRow = $table->header()->row();
		$headerRow->cell( '' ); // first cell...
		$rotateHtmlWrapper = function( $innerHtml ) {
			return '<div>' . $innerHtml . '</div>';
		};
		foreach ( $metaGroupKeys as $metaKey ) {
			foreach ( $this->data->getGroupsInMetaGroup( $metaKey ) as $group ) {
				$headerRow
					->cell( $rotateHtmlWrapper ( $this->metaGroupFormatters[$metaKey]( $group ) ) )
					->raw()
					->classes( [ 'group-name', 'rotate' ] );
			}
		}

		// Create the user rows
		$users = $this->data->getUsersInGroup( $this->sourceMetaGroup, $this->sourceGroup );
		foreach ( $users as $user ) {
			/** @var BodyRow $userRow */
			$userRow = $table->body()->row();
			$userRow->cell( $this->getHtmlForUser( $user ) )->raw();

			foreach( $metaGroupKeys as $metaKey ) {
				foreach ( $this->data->getGroupsInMetaGroup( $metaKey ) as $group ) {
					$userInGroup = $this->data->userIsInGroup( $user, $metaKey, $group );
					if ( $userInGroup === true ) {
						$userRow->cell( 'Yes' )->class( 'access-yes' );
					}
					if ( $userInGroup === false ) {
						$userRow->cell( '' ) ->class( 'access-no' );
					}
					if ( $userInGroup === null ) {
						$userRow->cell( '?' ) ->class( 'access-unknown' );
					}
				}
			}
		}

		return $table;
	}

	private function getHtmlForUser ( $user ) {
		return '<a href="https://ldap.toolforge.org/user/' . $user . '">' . $user . '<a/>';
	}

}
