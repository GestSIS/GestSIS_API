<?php

namespace App\Domaine\API;

use Illuminate\Support\Facades\Config;

class AdminService
{
    protected $service;

    public function __construct(SisParamService $service)
    {
        $this->service = $service;
    }

    public function sisContacts()
    {
        // Iteration sur toutes les bases de données
        $dbs = config('database.dbs');
        $res = [];
        foreach ($dbs as $db) {
            Config::set('database.default', 'db_' . $db);
            $res[$db] = $this->service->contacts();
        }
        return $res;
    }
}
