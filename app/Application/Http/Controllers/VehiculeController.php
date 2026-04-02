<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Infrastructure\Models\Article;

class VehiculeController extends Controller
{
    public function index()
    {
        $vehicules = Article::join('materiel_types', 'articles.materiel_type_id', '=', 'materiel_types.id')
            ->where('materiel_types.type', '=', MaterielTypeBusiness::TYPE_VEHICULE)
            ->get(['articles.*']);

        return response()->json(['data' => $vehicules]);
    }
}
