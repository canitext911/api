<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LocationController extends Controller
{
    private $apiEndpoint = null;

    /**
     * LocationController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->apiEndpoint = \env('CIT_IP_LOCATION_ENDPOINT');

        if (!$this->apiEndpoint) {
            throw new \Error('.env missing IP geolocation endpoint');
        }
    }

    /**
     * Fetch geolocation from IP
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $clientIp = $request->get('ip');

        if ($clientIp === null) {
            $clientIp = $_SERVER['REMOTE_ADDR'];

            // Grab actual client IP, this will be the first in
            // a comma-delimited list on Heroku
            // See https://stackoverflow.com/a/37061471/2535504
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $clientIp = \array_first(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            }
        }
        
        $apiEndpoint = str_replace('${IP_ADDR}', $clientIp, $this->apiEndpoint);

        $jsonResponse = \file_get_contents($apiEndpoint);

        if ($jsonResponse !== false) {
            $response = \json_decode($jsonResponse, true);

            if (($response['status'] ?? null) === 'success') {
                return Response::json([
                    'ip'           => $clientIp,
                    'zip'          => (int)$response['zip'] ?? null,
                    'city'         => $response['city'] ?? null,
                    'raw_response' => $response
                ], 200);
            } else {
                return Response::json([
                    'status'       => 'Service Unavailable',
                    'raw_response' => $response
                ], 503);
            }
        }

        return Response::json([
            'status'   => 'Remote Service Unavailable',
            'endpoint' => $this->apiEndpoint
        ], 502);
    }
}
