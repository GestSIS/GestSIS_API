<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use App\Support\Sis;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function validateEmail(Request $request)
    {
        $email = $request->input('email');
        if (!is_string($email) || $email === '') {
            return response()->json(['data' => []]);
        }

        $res = array_filter(Sis::each(fn() => Sapeur::whereIn('type', [SapeurBusiness::TYPE_SAPEUR, SapeurBusiness::TYPE_CIVIL])
                ->where('email', $email)
                ->get(['id', 'email'])
                ->first(fn(Sapeur $sapeur) => self::sameEmail($sapeur->email, $email))?->id));

        return response()->json(['data' => $res]);
    }

    /**
     * Comparaison stricte (à la casse près) de deux emails.
     *
     * La collation MySQL (utf8mb4_unicode_ci) ignore les accents : `exämple.ch`
     * y est égal à `example.ch`. Le `where('email', …)` ne sert donc que de
     * pré-filtre : sans ce contrôle, un compte créé avec une adresse homographe
     * accentuée serait lié au sapeur d'un tiers.
     */
    private static function sameEmail(?string $emailSapeur, string $email): bool
    {
        return $emailSapeur !== null && mb_strtolower(trim($emailSapeur)) === mb_strtolower(trim($email));
    }

    public function listeIdentitesActives()
    {
        $res = Sis::each(function () {
            return Sapeur::whereIn('type', [SapeurBusiness::TYPE_SAPEUR, SapeurBusiness::TYPE_CIVIL])
                ->where('actif', true)
                ->pluck('email', 'id')
                ->all();
        });

        return response()->json(['data' => $res]);
    }
}
