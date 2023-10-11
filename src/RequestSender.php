<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

/**
 * @licence BSD-3-Clause
 */
interface RequestSender {

	public function request( string $url, array $postParams ): string;

}