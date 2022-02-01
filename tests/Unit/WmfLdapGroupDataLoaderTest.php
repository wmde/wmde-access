<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview\Tests\Unit;

use FileFetcher\SimpleFileFetcher;
use FileFetcher\StubFileFetcher;
use FileFetcher\ThrowingFileFetcher;
use PHPUnit\Framework\TestCase;
use WMDE\PermissionsOverview\WmfLdapGroupDataLoader;

/**
 * @licence BSD-3-Clause
 */
class WmfLdapGroupDataLoaderTest extends TestCase {

	public function testReadsUsersFromGroupDataHtml() {
		$groupDataHtml = ( new SimpleFileFetcher() )->fetchFile( __DIR__ . '/../Fixtures/wmf-lda-group-data.html' );

		$loader = new WmfLdapGroupDataLoader( new StubFileFetcher( $groupDataHtml ) );

		$users = $loader->getUsersInGroup( 'test-group' );

		$this->assertEquals(
			[
				'janepublic',
				'joe-shmoe',
				'coolperson'
			],
			$users
		);
	}

	public function testGivenNoUserDataInHtml_returnsEmptyUserList() {
		$loader = new WmfLdapGroupDataLoader( new StubFileFetcher( 'foo' ) );

		$users = $loader->getUsersInGroup( 'test-group' );

		$this->assertEmpty( $users );
	}

	public function testGivenErrorFetchingData_returnsEmptyUserList() {
		$loader = new WmfLdapGroupDataLoader( new ThrowingFileFetcher() );

		$users = $loader->getUsersInGroup( 'test-group' );

		$this->assertEmpty( $users );
	}

}
