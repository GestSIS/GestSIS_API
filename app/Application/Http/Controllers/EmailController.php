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

        $res = array_filter(Sis::each(fn() => Sapeur::whereIn('type', [SapeurBusiness::TYPE_SAPEUR, SapeurBusiness::TYPE_CIVIL])
                ->where('email', $email)
                ->first(['id'])?->id));

        return response()->json(['data' => $res]);
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
