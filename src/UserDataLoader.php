<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

/**
 * @licence BSD-3-Clause
 */
class UserDataLoader {

	private $columnDefinitions;

	/** @var GroupMetadata[] $groupDefinitions */
	private $groupDefinitions;

	private $wmfLdapGroupDataLoader;
	/**
	 * @var WmfLdapPuppetGroupDataLoader
	 */
	private $wmfLdapPuppetGroupDataLoader;
	/**
	 * @var LocalFileGroupDataLoader
	 */
	private $localFileGroupDataLoader;

	public function __construct(
		ColumnDefinitions $columnDefinitions,
		array $groupDefinitions,
		WmfLdapGroupDataLoader $wmfLdapGroupDataLoader,
		WmfLdapPuppetGroupDataLoader $wmfLdapPuppetGroupDataLoader,
		LocalFileGroupDataLoader $localFileGroupDataLoader
	) {
		$this->columnDefinitions = $columnDefinitions;
		$this->groupDefinitions = $groupDefinitions;
		$this->wmfLdapGroupDataLoader = $wmfLdapGroupDataLoader;
		$this->wmfLdapPuppetGroupDataLoader = $wmfLdapPuppetGroupDataLoader;
		$this->localFileGroupDataLoader = $localFileGroupDataLoader;
	}

	public function loadDataOfUsersFromGroup( string $sourceGroup ): array {
		$userNames = $this->wmfLdapGroupDataLoader->getUsersInGroup( $sourceGroup );

		$groupMembers = $this->loadGroupMembers();

		$userData = [];

		foreach ( $groupMembers as $group => $memberList ) {
			foreach ( $userNames as $user ) {
				$userData[$user][$group] = in_array( $user, $memberList );
			}
		}

		return $userData;
	}

	private function loadGroupMembers(): array {
		$groupMembers = [];
		foreach ( $this->columnDefinitions->getCategories() as $category ) {
			foreach ( $this->columnDefinitions->getGroupsFromCategory( $category ) as $groupName ) {
				$group = $this->groupDefinitions[$groupName];
				if ( $group->getType() === SiteConfig::GROUP_TYPE_WMF_LDAP ) {
					$ldapGroup = $group->getName();
					// TODO: Move this magic out of here. Exact LDAP group name should become a part of config probably
					$ldapGroup = str_replace( 'ldap-', '', $ldapGroup );
					$groupMembers[$group->getName()] = $this->wmfLdapGroupDataLoader->getUsersInGroup( $ldapGroup );
				} elseif ( $group->getType() === SiteConfig::GROUP_TYPE_WMF_LDAP_PUPPET ) {
					$groupMembers[$group->getName()] = $this->wmfLdapPuppetGroupDataLoader->getUsersInGroup( $group->getName() );
				} elseif ( $group->getType() === SiteConfig::GROUP_TYPE_LOCAL_FILE ) {
					$groupMembers[$group->getName()] = $this->localFileGroupDataLoader->getUsersInGroup( $group->getId() );
				}
			}
		}
		return $groupMembers;
	}

}
