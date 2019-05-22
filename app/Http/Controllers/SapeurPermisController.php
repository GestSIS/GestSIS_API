<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sapeur;

class SapeurPermisController extends Controller
{

    /**
     * Return the permis
     *
     * @param int $sapeur_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(int $sapeur_id)
    {
        $permis = Sapeur::find($sapeur_id)->permis()->with('PermisType')->get();

        return response()->json(['data' => $permis]);
    }
}
