<?php

namespace App\Application\Http\Controllers;

use App\Models\Sms;

class ExerciceSmsController extends Controller
{
    public function index(int $exerciceId)
    {
        $sms = Sms::with('smsNumeros.sapeur')->where('exercice_id', '=', $exerciceId)->get();
        return response()->json(['data' => $sms]);
    }
}
