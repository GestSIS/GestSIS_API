<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailController extends Controller
{
    public function validateEmail(Request $request)
    {
        $email = $request->input('email');

        $dbs = config('database.dbs');
        $res = [];
        foreach ($dbs as $db) {
            DB::reconnect('db_' . $db);
            $sapeur = DB::connection('db_' . $db)
                ->table('sapeurs')
                ->where('email', '=', $email)
                ->select('sapeurs.id')
                ->first();
            if (!is_null($sapeur)) {
                $res[$db] = $sapeur->id;
            }
            DB::disconnect('db_' . $db);
        }

        return response()->json(['data' => $res]);
    }
}
