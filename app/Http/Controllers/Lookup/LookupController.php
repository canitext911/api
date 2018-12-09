<?php

namespace App\Http\Controllers\Lookup;

use App\Http\Controllers\Controller;
use App\Http\Models\Psap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LookupController extends Controller
{
    /**
     * LookupController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Basic search
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Psap::query();

        // basic city + zip search
        if ($search = $request->input('search')) {
            if (\is_numeric($search)) {
                $query->where('zip', \intval($search));
            } else {
                $query->where('city', 'ILIKE', '%' . $search . '%')
                    ->orWhere('county', 'ILIKE', '%' . $search . '%')
                    ->orWhere('state', 'ILIKE', '%' . $search . '%')
                    ->orWhere('name', 'ILIKE', '%' . $search . '%');
            }
        }

        return Response::json($query->paginate(), 200);
    }

    /**
     * Retrieve record by id
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $psap = Psap::where('id', $id)->first();

        if ($psap !== null) {
            return Response::json($psap, 200);
        }

        return Response::json([
            'status' => 'Record Not Found'
        ], 404);
    }

    /**
     * Return records filtered by zip code
     *
     * @param int $zip
     * @return \Illuminate\Http\JsonResponse
     */
    public function byZip(int $zip)
    {
        return Response::json(Psap::where('zip', $zip)->paginate(), 200);
    }

    /**
     * Retrieve record by psap_id
     *
     * @param int $psapId
     * @return \Illuminate\Http\JsonResponse
     */
    public function byPsaId(int $psapId)
    {
        $psap = Psap::where('psap_id', $psapId)->first();

        if ($psap !== null) {
            return Response::json($psap, 200);
        }

        return Response::json([
            'status' => 'Record Not Found. Note the difference between `id` and `psap_id`'
        ], 404);
    }

    /**
     * Suggest similar queries for autocomplete
     *
     * @param Request $request
     */
    public function suggest(Request $request)
    {
        $query = Psap::select([
            'city',
            'zip',
            'state',
            'name'
        ])->inRandomOrder();

        if ($search = $request->input('search')) {
            $query->where('city', 'ILIKE', '%' . $search . '%')
                ->orWhere('zip', 'ILIKE', '%' . $search . '%');
        }

        return Response::json($query->paginate(4), 200);
    }
}
