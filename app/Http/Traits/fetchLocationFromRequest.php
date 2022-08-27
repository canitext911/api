<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

trait fetchLocationFromRequest
{
    /**
     * Fetch geolocation from request IP using external API
     *
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function fetchLocationFromRequest(Request $request)
    {
        $endpoint = \env('CIT_IP_LOCATION_ENDPOINT');

        if (!$endpoint) {
            throw new Exception('.env missing IP geolocation endpoint');
        }

        $clientIp = $request->get('ip');

        if ($clientIp === null) {
            $clientIp = $_SERVER['REMOTE_ADDR'];

            // Grab actual client IP, this will be the first in
            // a comma-delimited list on Heroku
            // See https://stackoverflow.com/a/37061471/2535504
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $clientIp = Arr::first(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            }
        }

        $parsedEndpoint = \str_replace('${IP_ADDR}', $clientIp, $endpoint);
        $jsonResponse   = \file_get_contents($parsedEndpoint);

        if ($jsonResponse === false) {
            // 502 -> Bad Gateway
            throw new Exception('Service is offline. Unable to access ' . $parsedEndpoint, 502);
        }

        $response = \json_decode($jsonResponse, true);

        if (($response['status'] ?? null) === 'success') {
            return [
                'ip'           => $clientIp,
                'zip'          => (int)$response['zip'] ?? null,
                'city'         => $response['city'] ?? null,
                'raw_response' => $response
            ];
        } else {
            // 503 -> Service Unavailable
            throw new Exception(
                'Service unavailable, server responded <<'
                . ($response['message'] ?? \json_encode($response)) . '>>',
                503
            );
        }
    }
}
