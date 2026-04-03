<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\AlarmesBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Sapeur;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AlarmeController extends Controller
{
    public function index(Request $request)
    {
        $force = $request->get('force', false);

        $token = null;
        try {
            $token = $request->bearerToken();
        } catch (Exception $e) {
            return response()->json(["error" => "Accès refusé"], 401);
        }
        $sisKey = $request->header('Sis-Key', Null);

        try {
            $response = Http::withHeaders([
                'Sis-Key' => $sisKey,
                'Authorization' => 'Bearer ' . $token
            ])
                ->acceptJson()
                ->timeout(3)
                ->get(config('gestsis.alarm_url', '') . '/api/v1/alarm', ['force_update' => $force]);

            $alarmes = [];
            if ($response->successful()) {
                $alarmes = $response->object();
            }

            $sapeurs = Sapeur::with('telephones')->where('actif', '=', 1)->get(['nom', 'prenom', 'suffixe', 'id']);
            $interventions = AlarmesBusiness::resoudreAlarmes($alarmes, $sapeurs);
            return response()->json(['data' => $interventions]);
        } catch (Exception $e) {
            throw new ArrayException([], "Une erreur est survenue lors de la récupération des alarmes");
        }
    }
}
