<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\MaterielType;
use Illuminate\Http\Request;

class MaterielTypeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = MaterielType::with(['tuyau', 'batterie'])->orderBy('tri', 'asc')->get();
        return response()->json(['data' => $types]);
    }

    public function statuts()
    {
        return response()->json(['data' => MaterielTypeBusiness::calculerStatutsPeremption()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'materiel_categorie_id' => 'integer|required',
            'type' => 'integer|required',
            'est_numerote' => 'boolean|required',
            'est_attribuable' => 'boolean|required',
            'est_taillee' => 'boolean|required',
            'est_lavable' => 'boolean|required',
            'a_batterie' => 'boolean|required',
            'prix' => 'string|nullable',
            'fournisseur' => 'string|nullable',
            'numero_fournisseur' => 'string|nullable',
            'reparateur' => 'string|nullable',
            // 'a_controller' => 'string',
            'remarque' => 'string|nullable',
            'prefix' => 'string|nullable',
            'est_perimable' => 'boolean|nullable',
            'duree_peremption' => 'integer|min:1|nullable|required_if:est_perimable,true',
            'batterie.nombre' => 'integer|nullable',
            'batterie.batterie_type_id' => 'integer|nullable',
            'tuyau.tuyau_diametre_id' => 'integer|nullable',
            'tuyau.longeur' => 'integer|nullable',
            'tuyau.separement' => 'boolean|nullable',
        ]);

        $type = MaterielTypeBusiness::createProduct($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'materiel_categorie_id' => 'integer|required',
            'type' => 'integer|required',
            'est_numerote' => 'boolean|required',
            'est_attribuable' => 'boolean|required',
            'est_taillee' => 'boolean|required',
            'est_lavable' => 'boolean|required',
            'a_batterie' => 'boolean|required',
            'prix' => 'string|nullable',
            'fournisseur' => 'string|nullable',
            'numero_fournisseur' => 'string|nullable',
            'reparateur' => 'string|nullable',
            // 'a_controller' => 'string',
            'remarque' => 'string|nullable',
            'prefix' => 'string|nullable',
            'est_perimable' => 'boolean|nullable',
            'duree_peremption' => 'integer|min:1|nullable|required_if:est_perimable,true',
            'batterie.nombre' => 'integer|nullable',
            'batterie.batterie_type_id' => 'integer|nullable',
            'tuyau.tuyau_diametre_id' => 'integer|nullable',
            'tuyau.longeur' => 'integer|nullable',
            'tuyau.separement' => 'boolean|nullable',
        ]);

        $type = MaterielTypeBusiness::editProduct($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        $type = MaterielTypeBusiness::deleteProduct($id);
        return response()->json(['data' => $type]);
    }
}
