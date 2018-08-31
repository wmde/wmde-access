<?php

namespace WmdeAccess;

class GroupsData {

	private $metaGroupNames;
	private $groupMap;
	private $userMap;

	public function __construct( array $metaGroupNames, array $groupMap, array $userMap ) {
		$this->metaGroupNames = $metaGroupNames;
		$this->groupMap = $groupMap;
		$this->userMap = $userMap;
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

	public function getUsers() {
		return array_keys( $this->userMap );
	}

	public function userIsInGroup( $user, $metaKey, $group ) {
		return in_array( $group, $this->userMap[$user][$metaKey] );
	}

}
