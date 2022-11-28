<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\ComptabiliteParamService;

class IndemniteCoursTypeController extends Controller
{

    public function __construct(ComptabiliteParamService $service)
    {
        $this->service = $service;
    }

    /**
     * Créer un fichier iso20022 pour un type
     * 
     * @param int $id id du type pour lequelle le fichier doit être créé
     */
    public function index()
    {
        return $this->service->indemnitesCoursTypes();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'ecriture_categorie_id' => 'integer|required',
            'fonctions.*.type' => 'numeric|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
            'fonctions.*.type_unite_id' => 'integer|required',
        ]);

        $indemnite = $this->service->ajouterIndemniteCoursType($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'ecriture_categorie_id' => 'integer|required',
            'fonctions.*.type' => 'integer|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
            'fonctions.*.type_unite_id' => 'integer|required',
        ]);

        $indemnite = $this->service->modifierIndemniteCoursType($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    /**
     * Retourne un type
     * 
     * @param int $id id du type souhaité
     */
    public function destroy($id)
    {
        $this->service->supprimerIndemniteCoursType($id);
        return response()->json(['data' => 'ok']);
    }
}
