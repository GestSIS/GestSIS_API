<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MatPersoService;
use Illuminate\Http\Request;

class MatPersoController extends Controller
{
    protected $service;

    public function __construct(MatPersoService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $materiels = $this->service->materiels();

        return response()->json(['data' => $materiels]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function aRecuperer()
    {
        $materiels = $this->service->aRecuperer();

        return response()->json(['data' => $materiels]);
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
            'materiels.*.taille' => 'string|nullable',
            'materiels.*.remarque' => 'string|nullable',
            'materiels.*.materiel_type_id' => 'integer|nullable',
            'materiels.*.materiel' => 'required|array',
            'materiels.*.materiel.quantite' => 'integer|nullable',
            'materiels.*.materiel.numero' => 'string|nullable',
            'materiels.*.materiel.achat' => 'string|nullable',
        ]);

        $materiels = $this->service->create($data['materiels']);
        return response()->json(['data' => $materiels]);
    }

    /**
     * Modifier du matériel
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'materiels.*.id' => 'required|integer',
            'materiels.*.taille' => 'string|nullable',
            'materiels.*.remarque' => 'string|nullable',
            'materiels.*.materiel' => 'required|array',
            'materiels.*.materiel.id' => 'required|integer',
            'materiels.*.materiel.quantite' => 'integer|nullable',
            'materiels.*.materiel.numero' => 'string|nullable',
            'materiels.*.materiel.achat' => 'string|nullable',
        ]);

        $materiels = $this->service->update($data['materiels']);
        return response()->json(['data' => $materiels]);
    }

    /**
     * Supprimer du matériel personnel
     */
    public function destroy(Request $request)
    {
        $data = $request->validate([
            'materielIds' => 'required|array',
            'materielIds.*' => 'required|integer',
        ]);

        $materiels = $this->service->delete($data['materielIds']);
        return response()->json(['data' => $materiels]);
    }
}
