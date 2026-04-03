<?php

namespace App\Application\Http\Controllers;

use App\Models\Sms;

class SmsController extends Controller
{
    public function index($exerciceComptableId)
    {
        // TODO: Filter by date regarding exercice comptable
        $sms = Sms::with('smsNumeros')->get();
        return response()->json(['data' => $sms]);
    }
}
