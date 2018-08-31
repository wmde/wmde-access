<?php

namespace WmdeAccess;

use Tlr\Tables\Elements\Rows\BodyRow;
use Tlr\Tables\Elements\Rows\HeaderRow;
use Tlr\Tables\Elements\Table;

class GroupsPage {

	private $data;

	public function __construct( GroupsData $data ) {
		$this->data = $data;
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
		foreach ( $this->data->getUsers() as $user ) {
			/** @var BodyRow $userRow */
			$userRow = $table->body()->row();
			$userRow->cell( $this->getHtmlForUser( $user ) )->raw();

			foreach( $metaGroupKeys as $metaKey ) {
				foreach ( $this->data->getGroupsInMetaGroup( $metaKey ) as $group ) {
					if ( $this->data->userIsInGroup( $user, $metaKey, $group ) ) {
						$userRow->cell( 'Yes' )->class( 'access-yes' );
					} else {
						$userRow->cell( '' ) ->class( 'access-no' );
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

		return $group;
	}

	private function getHtmlForUser ( $user ) {
		return '<a href="https://tools.wmflabs.org/ldap/user/' . $user . '">' . $user . '<a/>';
	}

}
