<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\RecrutementTokenBusiness;
use App\Domaine\Business\SapeurBusiness;
use App\Models\Civilite;
use App\Models\Localite;
use App\Models\PermisType;
use App\Models\SisParam;
use App\Models\TelephoneType;
use App\Support\Sis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class RecrutementController extends Controller
{
    /**
     * Formulaire public d'auto-inscription des recrues : accessible sans authentification JWT,
     * protégé par un jeton de recrutement temporaire propre au SIS (cf. IcsController pour le même patron).
     */
    private function selectionnerBase(string $sisKey): void
    {
        if (!App::environment('testing')) {
            if (!Sis::isValid($sisKey)) {
                abort(404);
            }

            Sis::use($sisKey);
        }
    }

    /**
     * Vérifie la validité du jeton et fournit les données de référence nécessaires au formulaire
     * (civilités, localités, types de téléphone) : ces listes sont normalement derrière
     * une authentification, mais le formulaire de recrutement est public.
     */
    public function show(string $sisKey, string $token)
    {
        $this->selectionnerBase($sisKey);

        if (!RecrutementTokenBusiness::verifierToken($token)) {
            return response()->json(['data' => ['valide' => false]]);
        }

        return response()->json(['data' => [
            'valide' => true,
            'sisNom' => SisParam::first()?->nom,
            'civilites' => Civilite::all(),
            'localites' => Localite::all(),
            'telephoneTypes' => TelephoneType::all(),
            'permisTypes' => PermisType::all(),
        ]]);
    }

    public function store(Request $request, string $sisKey, string $token)
    {
        $this->selectionnerBase($sisKey);

        if (!RecrutementTokenBusiness::verifierToken($token)) {
            abort(404);
        }

        $data = $request->validate([
            'nom' => 'required|string|min:2',
            'prenom' => 'required|string|min:2',
            'rue' => 'required|string|min:3',
            'no_rue' => 'required|string',
            'date_naissance' => 'required|date|before:' . date('Y-m-d'),
            'localite_id' => 'required|integer|exists:localites,id',
            'civilite_id' => 'required|integer|exists:civilites,id',
            'no_avs' => 'required|string',
            'cotisation_avs' => 'boolean',
            'profession' => 'required|string|max:80',
            'employeur' => 'required|string|max:150',
            'lieu_de_travail' => 'required|string|max:100',
            'email' => 'required|email',
            'iban' => 'required|string|max:100',
            'telephones' => 'required|array|min:1',
            'telephones.*.numero' => 'required|string',
            'telephones.*.telephone_type_id' => 'required|integer|exists:telephone_types,id',
            'telephones.*.priorite' => 'required|integer',
            'permis' => 'array',
            'permis.*.permis_type_id' => 'required|integer|exists:permis_types,id',
            'permis.*.date' => 'required|date',
        ]);

        $recrue = SapeurBusiness::createRecrue($data);

        return response()->json(['data' => ['id' => $recrue->id]]);
    }
}
