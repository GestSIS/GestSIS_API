<?php

namespace App\Domaine\API;

use App\Infrastructure\Models\LocaliteSis;
use App\Infrastructure\Models\SisContact;
use App\Infrastructure\Models\SisParam;
use Illuminate\Support\Facades\Config;

class AdminService
{
    public function sisContacts()
    {
        // Iteration sur toutes les bases de données
        $dbs = config('database.dbs');
        $res = [];
        foreach ($dbs as $db) {
            Config::set('database.default', 'db_' . $db);
            $res[$db] = SisContact::all();
        }
        return $res;
    }

    public function sisParams()
    {
        // Iteration sur toutes les bases de données
        $dbs = config('database.dbs');
        $res = [];
        foreach ($dbs as $db) {
            Config::set('database.default', 'db_' . $db);
            $res[$db] = SisParam::with(['sapeur', 'localite'])->first();
        }
        return $res;
    }

    public function sisLocalites()
    {
        // Iteration sur toutes les bases de données
        $dbs = config('database.dbs');
        $res = [];
        foreach ($dbs as $db) {
            Config::set('database.default', 'db_' . $db);
            $localites = LocaliteSis::with('localite')->get();
            $res[$db] = $localites;
        }
        return $res;
    }
}
