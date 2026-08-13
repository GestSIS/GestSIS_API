<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\RecrutementTokenBusiness;
use App\Models\RecrutementToken;
use Illuminate\Http\Request;

class RecrutementTokenController extends Controller
{
    /**
     * Jeton de recrutement actif du SIS courant (métadonnées uniquement, jamais le jeton en clair).
     */
    public function show()
    {
        $token = RecrutementTokenBusiness::tokenActif();
        if ($token === null) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => ['expire_at' => $token->expire_at]]);
    }

    /**
     * Génère (ou regénère) le jeton de recrutement, en invalidant l'éventuel jeton précédent.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'duree_heures' => 'required|integer|min:1|max:24',
        ]);

        [$tokenEnClair, $token] = RecrutementTokenBusiness::genererToken($data['duree_heures']);

        return response()->json(['data' => [
            'token' => $tokenEnClair,
            'expire_at' => $token->expire_at,
        ]]);
    }

    /**
     * Invalide le jeton de recrutement actif sans en générer un nouveau.
     */
    public function destroy()
    {
        RecrutementToken::query()->delete();
        return response()->json(['data' => 'success']);
    }
}
