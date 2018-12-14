<?php

namespace App\Http\Controllers\Lookup;

use App\Http\Controllers\Controller;
use App\Http\Models\Psap;
use App\Http\Traits\isValidZip;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LookupController extends Controller
{
    use isValidZip;

    /**
     * Basic search
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Psap::query()
            ->orderBy('name');

        // basic city + zip search
        if ($search = $request->input('search')) {
            // postgres doesn't play nice with mixed types or invalid integers
            if ($this->isValidZip($search)) {
                $query->where('zip', \intval($search));
            } else {
                // state abbreviations, try to limit the noise
                if (\strlen($search) === 2) {
                    $query->where('state', 'ILIKE', '%' . $search . '%');
                } else {
                    $query->where('city', 'ILIKE', '%' . $search . '%')
                        ->orWhere('county', 'ILIKE', '%' . $search . '%')
                        ->orWhere('name', 'ILIKE', '%' . $search . '%');
                }
            }
        }

        return Response::json($query->paginate(), 200);
    }

    /**
     * Retrieve record by id
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $psap = Psap::where('id', $id)->first();

            if ($psap !== null) {
                return Response::json($psap, 200);
            }
        } catch (QueryException $exception) {
        }

        return Response::json([
            'errors' => ['Record Not Found']
        ], 404);
    }

    /**
     * Return records filtered by zip code
     *
     * @param string $zip
     * @return \Illuminate\Http\JsonResponse
     */
    public function byZip(string $zip)
    {
        if (!$this->isValidZip($zip)) {
            return Response::json([
                'errors' => ['Input must be numeric and five digits']
            ], 400);
        }

        return Response::json(Psap::where('zip', $zip)->paginate(), 200);
    }

    /**
     * Filter records by PSAP ID
     *
     * @param string $psapId
     * @return \Illuminate\Http\JsonResponse
     */
    public function byPsaId(string $psapId)
    {
        if (!\is_numeric($psapId)) {
            return Response::json([
                'errors' => ['Input must be an integer']
            ], 400);
        }

        try {
            $psap = Psap::where('psap_id', $psapId)->first();

            if ($psap !== null) {
                return Response::json($psap, 200);
            }
        } catch (QueryException $exception) {
        }

        return Response::json([
            'errors' => ['Record Not Found. Note the difference between `id` and `psap_id`']
        ], 404);
    }
}
