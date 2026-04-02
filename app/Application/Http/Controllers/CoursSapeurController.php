<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\CoursSapeur;
use App\Infrastructure\Models\ExerciceComptable;
use Illuminate\Http\Request;

class CoursSapeurController extends Controller
{
    public function index(Request $request, $exerciceComptableId)
    {
        // Check si permission comptabilite
        $admin = $request->attributes->get('admin', false);
        $permissions = $request->attributes->get('permissions', []);

        $avecEcritures = $admin || in_array('comptabilite.lecture', $permissions);

        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        if ($exerciceComptable == null) {
            return response()->json(['data' => []]);
        }

        $data = CoursSapeur::with($avecEcritures ? ['cours', 'ecritures'] : [])->where([
            ['date', '>=', $exerciceComptable->debut],
            ['date', '<=', $exerciceComptable->fin],
        ])->orderBy('date')->get();

        return response()->json(['data' => $data]);
    }
}
