<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\Telephone;
use Illuminate\Http\Response;

class TelephoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $telephones = Telephone::all();

        return response()->json(['data' => $telephones]);
    }
}
