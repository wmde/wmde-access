<?php

namespace WmdeAccess;

use Tlr\Tables\Elements\Rows\BodyRow;
use Tlr\Tables\Elements\Rows\HeaderRow;
use Tlr\Tables\Elements\Table;

class GroupsPage {

	private $data;
	private $headerHtml;
	private $metaGroupFormatters;
	private $sourceMetaGroup;
	private $sourceGroup;

	public function __construct(
		GroupsData $data,
		$headerHtml,
		$metaGroupFormatters,
		$sourceMetaGroup,
		$sourceGroup ) {
		$this->data = $data;
		$this->headerHtml = $headerHtml;
		$this->metaGroupFormatters = $metaGroupFormatters;
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
		$s .= $this->headerHtml;
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
