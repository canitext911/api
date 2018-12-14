<?php

namespace App\Http\Controllers\Recent;

use App\Http\Controllers\Controller;
use App\Http\Models\Psap;
use Illuminate\Support\Facades\Response;

class RecentController extends Controller
{
    /**
     * Index ordered by readiness date DESC
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $query = Psap::orderBy('ready_at', 'DESC');
        return Response::json($query->paginate(5), 200);
    }
}
