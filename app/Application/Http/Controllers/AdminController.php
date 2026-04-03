<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\LocaliteSis;
use App\Infrastructure\Models\SisContact;
use App\Infrastructure\Models\SisParam;
use Illuminate\Support\Facades\Config;

class AdminController extends Controller
{
    public function sisContacts()
    {
        $dbs = config('database.dbs');
        $res = [];
        foreach ($dbs as $db) {
            Config::set('database.default', 'db_' . $db);
            $res[$db] = SisContact::all();
        }
        return response()->json(['data' => $res]);
    }

    public function sisParams()
    {
        $dbs = config('database.dbs');
        $res = [];
        foreach ($dbs as $db) {
            Config::set('database.default', 'db_' . $db);
            $res[$db] = SisParam::with(['sapeur', 'localite'])->first();
        }
        return response()->json(['data' => $res]);
    }

    public function sisLocalites()
    {
        $dbs = config('database.dbs');
        $res = [];
        foreach ($dbs as $db) {
            Config::set('database.default', 'db_' . $db);
            $res[$db] = LocaliteSis::with('localite')->get();
        }
        return response()->json(['data' => $res]);
    }
}
