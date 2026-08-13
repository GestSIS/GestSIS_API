<?php

namespace App\Domaine\Business;

use App\Models\RecrutementToken;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class RecrutementTokenBusiness
{
    private static function estExpire(RecrutementToken $token): bool
    {
        return Date::now()->greaterThan($token->expire_at);
    }

    /**
     * Retourne le jeton de recrutement actif courant (non expiré), ou null s'il n'y en a pas.
     */
    public static function tokenActif(): ?RecrutementToken
    {
        $token = RecrutementToken::first();
        if ($token === null || self::estExpire($token)) {
            return null;
        }

        return $token;
    }

    /**
     * Génère un nouveau jeton (invalide l'éventuel jeton précédent, un seul actif à la fois).
     *
     * @return array{0: string, 1: RecrutementToken} Le jeton en clair (à afficher une seule fois) et le modèle créé.
     */
    public static function genererToken(int $dureeHeures): array
    {
        RecrutementToken::query()->delete();

        $tokenEnClair = Str::random(48);
        $token = RecrutementToken::create([
            'token' => hash('sha256', $tokenEnClair),
            'expire_at' => Date::now()->addHours($dureeHeures),
        ]);

        return [$tokenEnClair, $token];
    }

    /**
     * Vérifie qu'un jeton en clair correspond au jeton actif et n'est pas expiré.
     */
    public static function verifierToken(string $tokenEnClair): bool
    {
        $token = self::tokenActif();
        if ($token === null) {
            return false;
        }

        return hash_equals($token->token, hash('sha256', $tokenEnClair));
    }
}
