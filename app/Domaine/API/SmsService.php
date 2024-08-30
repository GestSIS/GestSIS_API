<?php

namespace App\Domaine\API;

use App\Domaine\Business\AspsmsBusiness;
use App\Infrastructure\Models\Sms;

class SmsService
{
    protected $business;

    public function __construct(AspsmsBusiness $business)
    {
        $this->business = $business;
    }

    public function smsParExerciceComptable(int $exerciceComptableId)
    {
        // TODO: Filter by date regarding exercice comptable
        return Sms::all();
    }

    public function smsParExercice(int $exerciceId)
    {
        return Sms::where('exercice_id', '=', $exerciceId)->get();
    }
}
