<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\TravauxParamService;
use Illuminate\Http\Request;

class TravailTypeController extends Controller
{
    protected $service;

    public function __construct(TravauxParamService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $avecTarifs = $admin || in_array('fiche_travail.config', $perms) || in_array('comptabilite.lecture', $perms);

        $type = $this->service->travailTypes($avecTarifs);

        return response()->json(['data' => $type]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'actif' => 'required|boolean',
            'ecriture_categorie_id' => 'required|integer',
            'type_unite_id' => 'integer|required',
            'fonctions.*.type' => 'numeric|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
        ]);

        $type = $this->service->ajouterType($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'actif' => 'required|boolean',
            'ecriture_categorie_id' => 'required|integer',
            'type_unite_id' => 'integer|required',
            'fonctions.*.type' => 'numeric|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
        ]);

        $type = $this->service->modifierType($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        $type = $this->service->supprimerType($id);
        return response()->json(['data' => $type]);
    }
}
