<?php

namespace WmdeAccess;

class GroupsData {

	private $metaGroupNames;
	private $groupMap;

	public function __construct( array $metaGroupNames, array $groupMap ) {
		$this->metaGroupNames = $metaGroupNames;
		$this->groupMap = $groupMap;
	}

	public function getMetaGroupKeys() {
		return array_keys( $this->metaGroupNames );
	}

	public function getMetaGroupText( $key ) {
		return $this->metaGroupNames[$key];
	}

	public function getNumberOfGroupsInMetaGroup( $key ) {
		return count( $this->groupMap[$key] );
	}

	public function getGroupsInMetaGroup( $key ) {
		return array_keys( $this->groupMap[$key] );
	}

	public function getUsersInGroup ( $metaKey, $group ) {
		return $this->groupMap[$metaKey][$group];
	}

	public function userIsInGroup( $user, $metaKey, $group ) {
		return in_array( $user, $this->groupMap[$metaKey][$group] );
	}

}
