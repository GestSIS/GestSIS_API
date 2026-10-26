<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\TravauxParamBusiness;
use App\Models\TravailType;
use Illuminate\Http\Request;

class TravailTypeController extends Controller
{
    private const MESSAGE_LIEN_OBLIGATOIRE = 'Un lien obligatoire nécessite au moins un exercice ou une intervention disponible';

    /**
     * Un lien obligatoire n'a de sens que si au moins un type d'événement est autorisé.
     *
     * @param array<string, mixed> $data
     */
    private static function lienObligatoireCoherent(array $data): bool
    {
        if (!($data['lien_obligatoire'] ?? false)) {
            return true;
        }

        return ($data['lien_exercice'] ?? false) || ($data['lien_intervention'] ?? false);
    }

    public function index(Request $request)
    {
        $perms = $request->attributes->get('permissions', []);
        $avecTarifs = in_array('fiche_travail.config', $perms) || in_array('comptabilite.lecture', $perms);

        $type = $avecTarifs ? TravailType::with('fonctions')->get() : TravailType::all();

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
            'lien_exercice' => 'boolean',
            'lien_intervention' => 'boolean',
            'lien_obligatoire' => 'boolean',
        ]);
        if (!self::lienObligatoireCoherent($data)) {
            return response()->json(['message' => self::MESSAGE_LIEN_OBLIGATOIRE], 422);
        }

        $type = TravauxParamBusiness::ajouterType($data);
        return response()->json(['data' => $type], 201);
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
            'lien_exercice' => 'boolean',
            'lien_intervention' => 'boolean',
            'lien_obligatoire' => 'boolean',
        ]);
        if (!self::lienObligatoireCoherent($data)) {
            return response()->json(['message' => self::MESSAGE_LIEN_OBLIGATOIRE], 422);
        }

        $type = TravauxParamBusiness::modifierType($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        $type = TravauxParamBusiness::supprimerType($id);
        return response()->json(null, 204);
    }
}
