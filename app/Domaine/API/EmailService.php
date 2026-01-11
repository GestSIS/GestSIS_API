<?php

namespace App\Domaine\API;

use App\Domaine\Business\ControleMedicalBusiness;
use Illuminate\Support\Facades\DB;

class EmailService
{
    protected $business;

    public function __construct(ControleMedicalBusiness $business)
    {
        $this->business = $business;
    }

    public function checkEmail($email)
    {
        // Iteration sur toutes les bases de données
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
        return $res;
    }
}
