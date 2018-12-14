<?php

namespace App\Http\Controllers\Nearby;

use App\Http\Controllers\Controller;
use App\Http\Models\Psap;
use App\Http\Traits\fetchLocationFromRequest;
use App\Http\Traits\isValidZip;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class NearbyController extends Controller
{
    use fetchLocationFromRequest;
    use isValidZip;

    /**
     * Get nearby PSAPs based on IP geolocation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $location = null;

        try {
            $location = $this->fetchLocationFromRequest($request);
        } catch (Exception $error) {
            return Response::json(['errors' => [$error->getMessage()]], $error->getCode());
        }

        $zip  = $location['zip'] ?? 0;
        $city = $location['city'] ?? null;

        $query = Psap::query();

        if ($this->isValidZip($zip)) {
            $query->where('zip', $zip);
        }

        if ($city !== null) {
            $query->orWhere('city', 'ILIKE', '%' . $city . '%');
        }

        return Response::json($query->paginate(5), 200);
    }
}
