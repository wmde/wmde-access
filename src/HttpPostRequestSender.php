<?php

declare( strict_types = 1 );

namespace WMDE\PermissionsOverview;

use RuntimeException;

/**
 * @licence BSD-3-Clause
 */
class HttpPostRequestSender {

    public function request( string $url, array $postParams ): string {
        $options = [
            'http' => [
                'method' => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query( $postParams )
            ],
        ];

        $context = stream_context_create( $options );
        $response = @file_get_contents( $url, false, $context );

        if ( is_string( $response ) ) {
            return $response;
        }

        throw new RuntimeException( "cannot load $url" );
    }


}