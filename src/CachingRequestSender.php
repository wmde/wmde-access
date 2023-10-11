<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

use Psr\SimpleCache\CacheException;
use Psr\SimpleCache\CacheInterface;

/**
 * @licence BSD-3-Clause
 */
class CachingRequestSender implements RequestSender {

	public function __construct( private RequestSender $requestSender, private CacheInterface $cache, private $ttl ) {

	}

	public function request(string $url, array $postParams): string {
		$requestResult = $this->getRequestResultFromCache( $url, $postParams );

		if ( $requestResult === null ) {
			return $this->requestAndCacheResults( $url, $postParams );
		}

		return $requestResult;
	}

	private function getRequestResultFromCache( string $url, array $params ) {
		try {
			return $this->cache->get( $this->makeCacheKey( $url, $params ) );
		} catch ( CacheException $exception ) {
			return null;
		}
	}

	private function requestAndCacheResults( string $url, array $params ) {
		$requestResults = $this->requestSender->request( $url, $params );

		$this->cache->set( $this->makeCacheKey( $url, $params ), $requestResults, $this->ttl );

		return $requestResults;
	}

	private function makeCacheKey( string $url, array $params ): string {
		return preg_replace( '/[^A-Za-z0-9\-]/', '_', $url) .
			'-' .
			preg_replace( '/[^A-Za-z0-9]/', '_', json_encode( $params ) ).
			substr(sha1( $url), 0, 5 );
	}

}