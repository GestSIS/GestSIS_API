<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehiculeController extends Controller
{
    /**
     * Véhicules : par défaut ceux qui ne sont pas supprimés et disponibles en
     * intervention ; avec `?tous=1`, tous les véhicules (supprimés et indisponibles compris).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::join('materiel_types', 'articles.materiel_type_id', '=', 'materiel_types.id')
            ->where('materiel_types.type', '=', MaterielTypeBusiness::TYPE_VEHICULE)
            ->with('emplacementRepresentee');

        if (!$request->boolean('tous')) {
            $query->nonSupprimes()->where('articles.disponible_intervention', true);
        }

        return response()->json(['data' => $query->get(['articles.*'])]);
    }
}
