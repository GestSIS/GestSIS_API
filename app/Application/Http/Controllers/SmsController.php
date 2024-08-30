<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SmsService;

class SmsController extends Controller
{
    protected $service;

    public function __construct(SmsService $service)
    {
        $this->service = $service;
    }

    public function index($exerciceComptableId)
    {
        $sms = $this->service->smsParExerciceComptable($exerciceComptableId);
        return response()->json(['data' => $sms]);
    }
}
