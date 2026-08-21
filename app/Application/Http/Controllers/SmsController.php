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

        $sms = Sms::with(['smsNumeros', 'exercice.categorie'])->where([
            ['date_programme', '>=', $exerciceComptable->debut],
            ['date_programme', '<=', $exerciceComptable->fin],
        ])->orWhereHas('exercice', function ($query) use ($exerciceComptableId) {
            $query->where('exercice_comptable_id', $exerciceComptableId);
        })->get();

        return response()->json(['data' => $sms]);
    }
}
