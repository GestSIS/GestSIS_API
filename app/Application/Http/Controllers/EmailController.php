<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function validateEmail(Request $request)
    {
        $email = $request->input('email');

        $res = [];
        foreach (config('database.dbs') as $db) {
            $sapeur = Sapeur::on('db_' . $db)
                ->whereIn('type', [SapeurBusiness::TYPE_SAPEUR, SapeurBusiness::TYPE_CIVIL])
                ->where('email', $email)
                ->first(['id']);
            if ($sapeur !== null) {
                $res[$db] = $sapeur->id;
            }
        }

        return response()->json(['data' => $res]);
    }

    public function listeSapeursActifs()
    {
        $res = [];
        foreach (config('database.dbs') as $db) {
            $res[$db] = Sapeur::on('db_' . $db)
                ->whereIn('type', [SapeurBusiness::TYPE_SAPEUR, SapeurBusiness::TYPE_CIVIL])
                ->where('actif', true)
                ->pluck('id')
                ->all();
        }

        return response()->json(['data' => $res]);
    }
}
