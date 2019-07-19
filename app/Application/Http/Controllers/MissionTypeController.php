<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\MissionType;
use Illuminate\Http\Response;

class MissionTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $missions = MissionType::all();

        return response()->json(['data' => $missions]);
    }
}
