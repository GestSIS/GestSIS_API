<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\Sapeur;
use App\Infrastructure\Models\SapeurTelephone;
use Illuminate\Http\Request;

class SapeurTelephoneController extends Controller
{
    protected $repo;
    protected $business;

    public function __construct(SapeurRepository $repo, SapeurBusiness $business)
    {
        $this->repo = $repo;
        $this->business = $business;
    }

    public function index(int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => $this->repo->getSapeurTelephonesById($sapeurId)]);
    }

    public function store(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'telephone_type_id' => 'required|integer|exists:telephone_types,id',
            'numero' => 'required|string|min:2',
            'priorite' => 'required|integer',
            'rta' => 'required|boolean',
        ]);

        $telephone = $this->business->addTelephone($sapeurId, $data);
        return response()->json(['data' => $telephone]);
    }

    public function update(Request $request, int $sapeurId, int $telephoneId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'id' => 'required|integer',
            'telephone_type_id' => 'integer|exists:telephone_types,id',
            'numero' => 'string|min:2',
            'priorite' => 'integer',
            'rta' => 'boolean',
        ]);

        if ($telephoneId !== $request->get('id')) {
            return response()->json(['error' => 'invalid telephone id'], 400);
        }
        if (!SapeurTelephone::where(['id' => $telephoneId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Téléphone non trouvé'], 404);
        }

        $telephone = $this->business->updateTelephone($sapeurId, $data);
        return response()->json(['data' => $telephone]);
    }

    public function destroy(int $sapeurId, int $telephoneId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!SapeurTelephone::where(['id' => $telephoneId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Téléphone non trouvé'], 404);
        }

        $this->business->removeTelephone($sapeurId, $telephoneId);
        return response()->json(['data' => 'success']);
    }
}
