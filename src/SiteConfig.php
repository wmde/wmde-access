<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

/**
 * @licence BSD-3-Clause
 */
class SiteConfig {

	private const CONFIG_KEY_GROUPS = 'groups';
	private const CONFIG_KEY_COLUMNS = 'columns';

	private const CONFIG_KEY_USERS = 'users';

	public const GROUP_TYPE_WMF_LDAP = 'wmf-ldap';
	public const GROUP_TYPE_WMF_LDAP_PUPPET = 'wmf-ldap-puppet';
	public const GROUP_TYPE_LOCAL_FILE = 'local-file';

	private $config;
	/**
	 * @var GroupDefinitionBuilder
	 */
	private $groupDefinitionBuilder;

	public function __construct(
		array $config,
		GroupDefinitionBuilder $groupDefinitionBuilder,
		private UserMetadataBuilder $userMetadataBuilder
	) {
		$this->config = $config;
		$this->groupDefinitionBuilder = $groupDefinitionBuilder;
	}

	public function getGroupDefinitions(): array {
		return $this->groupDefinitionBuilder->getGroups( $this->config[ self::CONFIG_KEY_GROUPS ] );
	}

	public function getColumnDefinitions(): ColumnDefinitions {
		return new ColumnDefinitions( $this->config[self::CONFIG_KEY_COLUMNS] );
	}

	public function getUsers(): array {
		return $this->userMetadataBuilder->getUserMetadata( $this->config[self::CONFIG_KEY_USERS] );
	}

}
