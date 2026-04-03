<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ControleMedicalBusiness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JustificatifController extends Controller
{
    public function show(int $controleId)
    {
        $justificatif = ControleMedicalBusiness::getJustificatif($controleId);

        $headers = array(
            'Content-Type: application/pdf',
            'Cache-Control: no-cache private',
            'Content-Description: File Transfer',
            'Content-Transfer-Encoding: binary'
        );
        return Storage::download($justificatif['path'], $justificatif['filename'], $headers);
    }

    public function store(Request $request, int $id)
    {
        if (!$request->hasFile('justificatif') || !$request->file('justificatif')->isValid()) {
            return response()->json(['error' => 'Fichier justificatif manquant']);
        }

        $file = $request->file('justificatif');
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        $justificatif = ControleMedicalBusiness::addJustificatif($id, $file, $sisKey);

        return response()->json(['data' => $justificatif]);
    }

    public function destroy(int $controleId)
    {
        ControleMedicalBusiness::removeJustificatif($controleId);

        return response()->json(['data' => "success"]);
    }
}
