<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\ExcuseType;
use Illuminate\Http\Request;

class ExcuseTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $excuseTypes = ExcuseType::all();

        return response()->json(['data' => $excuseTypes]);
    }
}
