<?php

namespace WmdeAccess;

use Tlr\Tables\Elements\Rows\BodyRow;
use Tlr\Tables\Elements\Rows\HeaderRow;
use Tlr\Tables\Elements\Table;

class GroupsPage {

	private $data;
	private $sourceMetaGroup;
	private $sourceGroup;

	public function __construct( GroupsData $data, $sourceMetaGroup, $sourceGroup ) {
		$this->data = $data;
		$this->sourceMetaGroup = $sourceMetaGroup;
		$this->sourceGroup = $sourceGroup;
	}

	public function getHtml() {
		$s = '';
		$s .= "<html>";
		$s .=  "<head>";
		$s .=  "<link rel=\"stylesheet\" href=\"https://tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/4.0.0-beta/css/bootstrap.min.css\">";
		$s .=  "<link rel=\"stylesheet\" href=\"main.css\">";
		$s .=  "</head>";
		$s .=  "<body>";
		$s .=  "<h1>WMDE groups</h1>";
		$s .=  "<p>Code for this tool can be found @ <a href='https://github.com/addshore/wmde-access' >https://github.com/addshore/wmde-access</a></p>";
		$s .=  $this->getTable()->render();
		$s .=  "</body>";
		$s .=  "</html>";
		return $s;
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
				$headerRow->cell( $rotateHtmlWrapper ( $this->formatGroup( $metaKey, $group ) ) )
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
						$userRow->cell( 'N/A' ) ->class( 'access-unknown' );
					}
				}
			}
		}

		return $table;
	}

	private function formatGroup( $metaKey, $group ) {
		// TODO maybe this formatting logic should be somewhere else?
		if ( $metaKey === META_GROUP_LDAP_CLOUD ) {
			$cloudVpsLinkHtmlGen = function ( $project ) {
				return '<a href="https://tools.wmflabs.org/openstack-browser/project/' . $project . '">' . $project . '</a>';
			};

			// If this is a cloud VPS project
			if ( substr( $group, 0, 8 ) === 'project-' ) {
				return $cloudVpsLinkHtmlGen( str_replace( 'project-', '', $group ) );
			}
			return $group;
		}

		if ( $group === 'Gerrit Managers' ) {
			return '<a href="https://gerrit.wikimedia.org/r/#/admin/groups/119,members" >' . $group . '</a>';
		}

		return $group;
	}

	private function getHtmlForUser ( $user ) {
		return '<a href="https://tools.wmflabs.org/ldap/user/' . $user . '">' . $user . '<a/>';
	}

}
