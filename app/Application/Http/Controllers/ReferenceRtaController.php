<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\RtaBusiness;
use Illuminate\Http\Request;

class ReferenceRtaController extends Controller
{
    public function demandes()
    {
        return response()->json(["data" => RtaBusiness::getDemandes()]);
    }

    public function downloadFichier($fichierId)
    {
        $fichier = RtaBusiness::downloadFichier($fichierId);
        if (!$fichier) {
            return response()->json(["message" => "Fichier non trouvé"], 404);
        }

        return response()->streamDownload(
            fn() => print ($fichier['content']),
            $fichier['filename'],
            ['Content-Type' => $fichier['content_type']]
        );
    }

    public function fichiers()
    {
        return response()->json(["data" => RtaBusiness::getFichiers()]);
    }

    public function getReferenceRta()
    {
        return response()->json(["data" => RtaBusiness::getReferenceRta()]);
    }

    public function getReferenceGestSis()
    {
        return response()->json(["data" => RtaBusiness::getReferenceGestSis()]);
    }

    public function setReference(Request $request)
    {
        $data = $request->validate([
            'sis' => 'string|required',
            'sapeurs' => 'array',
            'sapeurs.*.sapeur_id' => 'required|integer',
            'sapeurs.*.nom' => 'required|string',
            'sapeurs.*.prenom' => 'required|string',
            'sapeurs.*.suffixe' => 'nullable|string',
            'sapeurs.*.localite' => 'required|string',
            'sapeurs.*.adresse' => 'required|string',
            'sapeurs.*.fonction' => 'nullable|string',
            'sapeurs.*.date_naissance' => 'required|date',
            'sapeurs.*.groupes' => 'required|array|min:1',
            'sapeurs.*.groupes.*.no' => 'required|string',
            'sapeurs.*.numeros' => 'required|array|min:1',
            'sapeurs.*.numeros.*.numero' => 'required|string|max:20',
            'sapeurs.*.numeros.*.type' => 'required|in:Privé,Prof,Mobile',
            'sapeurs.*.numeros.*.tri' => 'required|integer',
        ]);

        return response()->json([
            "data" => RtaBusiness::setReference($data['sapeurs'])
        ]);
    }
}
