<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\EmplacementBusiness;
use App\Domaine\Business\MaterielBusiness;
use App\Infrastructure\Models\Emplacement;
use Illuminate\Http\Request;
use Nette\NotImplementedException;

class EmplacementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $emplacements = EmplacementBusiness::listEmplacements();
        return response()->json(['data' => $emplacements]);
    }

    public function store(Request $request)
    {
        throw NotImplementedException();
        // $data = $request->validate([
        //     'designation' => 'string|min:1|required',
        //     'remarque' => 'string',
        //     'tri' => 'integer|required',
        //     'est_etiquete' => 'boolean|required',
        //     'impression_inventaire' => 'boolean|required',
        //     'couleur_id' => 'integer|min:1|required',
        //     'parent_id' => 'integer|nullable|required',
        //     'statut' => 'integer|required',
        // ]);

        // $emplacement = $this->business->ajouterEmplacement($data);
        // return response()->json(['data' => $emplacement]);
    }

    public function update(Request $request, $id)
    {
        throw NotImplementedException();
        // $data = $request->validate([
        //     'designation' => 'string|min:1|required',
        //     'remarque' => 'string',
        //     'tri' => 'integer|required',
        //     'est_etiquete' => 'boolean|required',
        //     'impression_inventaire' => 'boolean|required',
        //     'couleur_id' => 'integer|min:1|required',
        //     'parent_id' => 'integer|nullable|required',
        //     'statut' => 'integer|required',
        // ]);

        // $emplacement = $this->business->modifierEmplacement($id, $data);
        // return response()->json(['data' => $emplacement]);
    }

    public function destroy($id)
    {
        throw NotImplementedException();
        // $emplacement = $this->business->supprimerEmplacement($id);
        // return response()->json(['data' => $emplacement]);
    }
}
