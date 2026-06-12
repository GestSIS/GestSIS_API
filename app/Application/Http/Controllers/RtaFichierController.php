<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\RtaBusiness;

class RtaFichierController extends Controller
{
    public function index()
    {
        return response()->json(["data" => RtaBusiness::getFichiers()]);
    }

    public function show($fichierId)
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
}
