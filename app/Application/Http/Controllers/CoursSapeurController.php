<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\CoursService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CoursSapeurController extends Controller
{
    private $service = null;

    public function __construct(CoursService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, $exerciceComptableId)
    {
        // Check si permission comptabilite
        $admin = $request->attributes->get('admin', false);
        $permissions = $request->attributes->get('permissions', []);

        $avecEcritures = $admin || in_array('comptabilite.lecture', $permissions);

        return response()->json(['data' => $this->service->coursSapeurs($exerciceComptableId, $avecEcritures)]);
    }
}
