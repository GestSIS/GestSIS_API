<?php

namespace App\Application\Http\Controllers;

use App\Application\Auth\TokenTools;
use App\Domaine\API\AlarmeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AlarmeController extends Controller
{
    protected $service;

    public function __construct(AlarmeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @param old Boolean True pour récupérer également récupérer les alarmes déjà validées
     * @param force Boolean True pour l'api d'alarme a récupérer les données depuis le serveur mail
     * @return Response
     */
    public function index(Request $request)
    {
        // Param
        $force = $request->get('force', false);
        $old = $request->get('old', false);

        $token = null;
        try {
            $token = $request->bearerToken();
        } catch (Exception $e) {
            return response()->json(["error" => "Accès refusé"], 401);
        }
        $sisKey = $request->header('Sis-Key', Null);

        $interventions = $this->service->listeAlarme($sisKey, $token, $force);
        return response()->json(['data' => $interventions]);
    }
}
