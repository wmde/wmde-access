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

	/**
	 * @param string $user
	 * @param string $metaKey one of the constants
	 * @param string $group
	 * @return bool|null bool if we know the user is in or not in the group, null if we don't know
	 */
	public function userIsInGroup( $user, $metaKey, $group ) {
		if ( $this->groupMap[$metaKey][$group] === null ) {
			return null;
		}
		return in_array( $user, $this->groupMap[$metaKey][$group] );
	}

}
