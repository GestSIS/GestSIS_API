<?php

namespace App\Support;

final class Telephone
{
    /**
     * Ramène un numéro au format international compact (+41791234567) pour comparer des numéros
     * saisis dans des formats différents (« 079 123 45 67 », « +41 79 … », « 0041 79 … »,
     * « +41 (0)79 … », « 079-123-45-67 »). Un numéro sans indicatif commençant par 0 est suisse.
     */
    public static function normaliser(string $numero): string
    {
        $saisie = trim($numero);
        $chiffres = preg_replace('/\D/', '', $saisie);
        if ($chiffres === '') {
            return '';
        }

        if (str_starts_with($saisie, '+')) {
            $normalise = '+' . $chiffres;
        } elseif (str_starts_with($chiffres, '00')) {
            $normalise = '+' . substr($chiffres, 2);
        } elseif (str_starts_with($chiffres, '0')) {
            $normalise = '+41' . substr($chiffres, 1);
        } else {
            $normalise = $chiffres;
        }

        // « +41 (0)79 … » : le 0 national ne doit pas suivre l'indicatif
        if (str_starts_with($normalise, '+410')) {
            $normalise = '+41' . substr($normalise, 4);
        }

        return $normalise;
    }
}
