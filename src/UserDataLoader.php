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
		private WmfPhabricatorGroupDataLoader $wmfPhabricatorGroupDataLoader,
		LocalFileGroupDataLoader $localFileGroupDataLoader
	) {
		$this->columnDefinitions = $columnDefinitions;
		$this->groupDefinitions = $groupDefinitions;
		$this->wmfLdapGroupDataLoader = $wmfLdapGroupDataLoader;
		$this->wmfLdapPuppetGroupDataLoader = $wmfLdapPuppetGroupDataLoader;
		$this->localFileGroupDataLoader = $localFileGroupDataLoader;
	}

	public function loadDataOfUsers( array $users ): array {
		$groupMembers = $this->loadGroupMembers();

		$userData = [];

		foreach ( $groupMembers as $group => $memberList ) {
			foreach ( $users as $userCanonicalName => $userMetadata ) {
				$userData[$userCanonicalName][$group] = in_array(
					$this->getUserNameForGroup( $userMetadata, $group ),
					$memberList
				);
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
					$ldapGroup = $group->getId();
					if ( $ldapGroup === '') {
						$ldapGroup = $group->getName();
					}
					$groupMembers[$group->getName()] = $this->wmfLdapGroupDataLoader->getUsersInGroup( $ldapGroup );
				} elseif ( $group->getType() === SiteConfig::GROUP_TYPE_WMF_LDAP_PUPPET ) {
					$groupMembers[$group->getName()] = $this->wmfLdapPuppetGroupDataLoader->getUsersInGroup( $group->getName() );
				} elseif ( $group->getType() === 'wmf-phabricator' ) {
					$groupExtraData = $group->getExtraData();
					$projectId = (string)$groupExtraData['project-id'];
					$groupMembers[$group->getName()] = $this->wmfPhabricatorGroupDataLoader->getUsersInGroup( $projectId );
				} elseif ( $group->getType() === SiteConfig::GROUP_TYPE_LOCAL_FILE ) {
					$groupMembers[$group->getName()] = $this->localFileGroupDataLoader->getUsersInGroup( $group->getId() );
				}
			}
		}
		return $groupMembers;
	}

	private function getUserNameForGroup( User $userMetadata, string $groupName ): string {
		$group = $this->groupDefinitions[$groupName];
		if ( $group->getType() === SiteConfig::GROUP_TYPE_WMF_LDAP ||  $group->getType() === SiteConfig::GROUP_TYPE_WMF_LDAP_PUPPET ) {
			return $userMetadata->getWmfLdapUsername();
		}
		if ( $group->getType() === 'wmf-phabricator' ) {
			return $userMetadata->getWmfPhabricatorUsername();
		}
		if ( $group->getType() === SiteConfig::GROUP_TYPE_LOCAL_FILE ) {
			// TODO: should be canonical name probably in the end
			return $userMetadata->getWmfLdapUsername();
		}

		return $userMetadata->getCanonicalName();
	}

}
