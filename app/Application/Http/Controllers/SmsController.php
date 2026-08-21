<?php

namespace App\Application\Http\Controllers;

use App\Models\ExerciceComptable;
use App\Models\Sms;

class SmsController extends Controller
{
    public function index($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        if ($exerciceComptable === null) {
            return response()->json(['data' => []]);
        }

        $sms = Sms::with('smsNumeros')->where([
            ['date_programme', '>=', $exerciceComptable->debut],
            ['date_programme', '<=', $exerciceComptable->fin],
        ])->get();

        return response()->json(['data' => $sms]);
    }
}
