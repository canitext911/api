<?php

namespace App\Http\Controllers\Suggest;

use App\Http\Controllers\Controller;
use App\Http\Models\Psap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SuggestController extends Controller
{
    /**
     * Suggest similar queries for autocomplete
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Psap::select([
            'city',
            'zip',
            'state',
            'name'
        ])->inRandomOrder();

        if ($search = $request->input('search')) {
            $query->where('city', 'ILIKE', '%' . $search . '%')
                ->orWhere('state', 'ILIKE', '%' . $search . '%')
                ->orWhere('name', 'ILIKE', '%' . $search . '%');

            if ($this->isValidZip($search)) {
                $query->orWhere('zip', 'ILIKE', '%' . $search . '%');
            }
        }

        return Response::json($query->paginate(4), 200);
    }
}
