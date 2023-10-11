<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

/**
 * @licence BSD-3-Clause
 */
class WmfPhabricatorGroupDataLoader {

	private const PHABRICATOR_PROJECT_SEARCH_URL = 'https://phabricator.wikimedia.org/api/project.search';
	private const PHABRICATOR_USER_SEARCH_URL = 'https://phabricator.wikimedia.org/api/user.search';
	private const PHABRICATOR_USER_SEARCH_LIMIT = 100;

	public function __construct(
        private RequestSender $requestSender,
		private string $apiToken
    ) {
    }

    public function getUsersInGroup( string $group ): array {
		$projectSearchParams = $this->getProjectUsersQueryParameters( $group );
		$projectSearchResponse = $this->requestSender->request( self::PHABRICATOR_PROJECT_SEARCH_URL, $projectSearchParams );
		$projectSearchResults = json_decode( $projectSearchResponse, true );
		$userPhids = [];
		foreach ( $projectSearchResults['result']['data'] as $projectData ) {
			foreach ( $projectData['attachments']['members']['members'] as $memberData ) {
				$userPhids[] = $memberData['phid'];
			}
		}

		$userNames = [];
		$userSearchAfterCursor = null;
		do {
			$userSearchParams = $this->getUserNameQueryParameters( $userPhids, $userSearchAfterCursor );
			$userSearchResponse = $this->requestSender->request( self::PHABRICATOR_USER_SEARCH_URL, $userSearchParams );
			$userSearchResult = json_decode( $userSearchResponse, true );
			$userSearchAfterCursor = $userSearchResult['result']['cursor']['after'];

			foreach ( $userSearchResult['result']['data'] as $userData ) {
				$userNames[] = $userData['fields']['username'];
			}
		} while ( $userSearchAfterCursor !== null );

		return $userNames;
    }

	private function getProjectUsersQueryParameters(string $group ): array {
		return [
			'constraints' => [ 'ids' => [ $group ] ],
			'attachments' => [ 'members' => true ],
			'api.token' => $this->apiToken,
		];
	}

	private function getUserNameQueryParameters( array $userPhids, ?string $after ): array {
		return [
			'constraints' => [ 'phids' => $userPhids ],
			'limit' => self::PHABRICATOR_USER_SEARCH_LIMIT,
			'after' => $after,
			'api.token' => $this->apiToken,
		];
	}

}