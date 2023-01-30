<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ImputationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ComptabiliteStatistiqueController extends Controller
{

    protected $service;

    public function __construct(ImputationService $service)
    {
        $this->service = $service;
    }

    /**
     * Return le nombre le montant et le nombre d'écriture par compte
     *
     * @param Request $request
     * @param int $exercice_comptable_id
     * @return Response
     */
    public function compte(int $exerciceComptableId)
    {
        $data = $this->service->statCompte($exerciceComptableId);

        return response()->json(['data' => $data]);
    }

    /**
     * Return le nombre le montant et le nombre d'écriture par catégorie comptable
     *
     * @param Request $request
     * @param int $exercice_comptable_id
     * @return Response
     */
    public function categorie($exerciceComptableId)
    {
        $data = $this->service->statCategorie($exerciceComptableId);

        return response()->json(['data' => $data]);
    }

    /**
     * Return le nombre le montant et le nombre d'écriture par module comptable
     *
     * @param Request $request
     * @param int $exercice_comptable_id
     * @return Response
     */
    public function module($exerciceComptableId)
    {
        $materiels = $this->service->statModule($exerciceComptableId);

        return response()->json(['data' => $materiels]);
    }
}
