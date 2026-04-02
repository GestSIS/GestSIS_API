<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\AspsmsBusiness;
use Illuminate\Http\Request;

class AspsmsController extends Controller
{
    public function credit()
    {
        $credit = AspsmsBusiness::getCredit();
        return response()->json(['data' => $credit]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string',
            // 'origin' => 'required|string', // Pas pour le moment
            'differe' => 'boolean',
            'date' => 'nullable|string',
            'contacts.*.numero' => 'required|string',
            'contacts.*.sapeurId' => 'nullable|integer',
            'exerciceId' => 'nullable|integer',
        ]);

        $params = AspsmsBusiness::send($data);

        return response()->json(['data' => $params]);
    }
}
