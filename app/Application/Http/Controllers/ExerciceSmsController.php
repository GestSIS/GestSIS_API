<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SmsService;

class ExerciceSmsController extends Controller
{
    protected $service;

    public function __construct(SmsService $service)
    {
        $this->service = $service;
    }

    public function index(int $exerciceId)
    {
        $sms = $this->service->smsParExerciceComptable($exerciceId);
        return response()->json(['data' => $sms]);
    }
}
