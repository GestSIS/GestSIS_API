<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MatPersoService;
use Illuminate\Http\Request;

class MatPersoEventController extends Controller
{
    protected $service;

    public function __construct(MatPersoService $service)
    {
        $this->service = $service;
    }

    /**
     * Créer un décompte
     * 
     * @param int $exerciceId - id de l'exercice
     * @param date $date - date de la création du décompte
     * @param boolean $deduction - true si les déduction doivent être faites sur ce paiement
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'events.*.materiel_event_type_id' => 'required|integer',
            'events.*.materiel_id' => 'required|integer',
            'events.*.date' => 'required|date',
            'events.*.remarque' => 'string|nullable',
            'events.*.succes' => 'boolean|nullable',
        ]);

        $materiels = $this->service->createEvents($data['events']);
        return response()->json(['data' => $materiels]);
    }
}
