<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Http\Traits\fetchLocationFromRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LocationController extends Controller
{
    use fetchLocationFromRequest;

    /**
     * Fetch geolocation from IP
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $location = $this->fetchLocationFromRequest($request);
            return Response::json($location, 200);
        } catch (Exception $error) {
            return Response::json([
                'errors' => [
                    $error->getMessage()
                ]
            ], $error->getCode());
        }
    }
}
