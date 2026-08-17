<?php

namespace App\Application\Http\Controllers;

use App\Models\IcsToken;
use App\Support\Sis;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IcsTokenController extends Controller
{
    /**
     * Récupération (avec création à la volée) des liens d'agenda ICS du sapeur connecté,
     * pour tous les SIS auxquels il appartient.
     */
    public function index(Request $request)
    {
        $token = $request->attributes->get('jwtToken');
        $sapeurs = $token !== null ? (array) $token->data->sapeurs : [];

        $res = Sis::each(function ($sisKey) use ($sapeurs) {
            $icsToken = IcsToken::firstOrCreate(
                ['sapeur_id' => $sapeurs[$sisKey]],
                ['token' => Str::random(48)]
            );

            return [
                'sis_key' => $sisKey,
                'url' => route('ics.show', ['sisKey' => $sisKey, 'token' => $icsToken->token]),
            ];
        }, array_keys($sapeurs));

        return response()->json(['data' => array_values($res)]);
    }

    /**
     * Régénération du lien d'agenda ICS du sapeur connecté pour un SIS donné.
     * Invalide l'ancien lien (les abonnements existants dans les clients calendrier cessent de fonctionner).
     */
    public function regenerate(Request $request, string $sisKey)
    {
        $token = $request->attributes->get('jwtToken');
        $sapeurs = $token !== null ? (array) $token->data->sapeurs : [];

        if (!array_key_exists($sisKey, $sapeurs)) {
            return response()->json(['error' => 'Sis inconnu'], 403);
        }

        Sis::use($sisKey);

        $icsToken = IcsToken::updateOrCreate(
            ['sapeur_id' => $sapeurs[$sisKey]],
            ['token' => Str::random(48)]
        );

        return response()->json(['data' => [
            'sis_key' => $sisKey,
            'url' => route('ics.show', ['sisKey' => $sisKey, 'token' => $icsToken->token]),
        ]]);
    }
}
