<?php

namespace App\Application\Http\Controllers;

use App\Domaine\SPI\SapeurRepository;
use Illuminate\Http\Request;

class MesInfosController extends Controller
{
    private $sapeurRepo;

    public function __construct(SapeurRepository $sapeurRepo)
    {
        $this->sapeurRepo = $sapeurRepo;
    }

    /**
     * Récupère les informations du sapeur
     */
    public function index(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->sapeurRepo->getSapeurDetailsById($sapeurId, ['telephones']);
        return response()->json(['data' => $data]);
    }

    public function fonctions(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->sapeurRepo->getSapeurFonctionsById($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function mutations(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->sapeurRepo->getSapeurMutationsById($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function grades(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->sapeurRepo->getSapeurGradesById($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function cours(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->sapeurRepo->getSapeurCoursById($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function permis(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->sapeurRepo->getSapeurPermisById($sapeurId);
        return response()->json(['data' => $data]);
    }

    public function groupes(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->sapeurRepo->getSapeurGroupesById($sapeurId);
        return response()->json(['data' => $data]);
    }
}
