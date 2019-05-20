<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sapeur;


class SapeurTelephoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param int $sapeur_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(int $sapeur_id)
    {
        $telephones = Sapeur::find($sapeur_id)->telephones()->with('TelephoneType')->get();

        return response()->json(['data' => $telephones]);
    }
}
