<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\Permis;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurPermisController extends Controller
{
    protected $repo;
    protected $business;

    public function __construct(SapeurRepository $repo, SapeurBusiness $business)
    {
        $this->repo = $repo;
        $this->business = $business;
    }

    public function index(int $sapeur_id)
    {
        if (!Sapeur::where('id', $sapeur_id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => Sapeur::find($sapeur_id)->permis()->get()]);
    }

    public function store(Request $request, int $id)
    {
        if (!Sapeur::where('id', $id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'permis_type_id' => 'required|integer|exists:permis_types,id',
            'date' => 'required|date|before:tomorrow'
        ]);

        $permis = $this->business->addPermis($id, $data);
        return response()->json(['data' => $permis]);
    }

    public function update(Request $request, int $id, int $permisId)
    {
        if (!Sapeur::where('id', $id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'id' => 'required|integer',
            'date' => 'required|date|before:tomorrow'
        ]);

        if ($permisId !== $request->get('id')) {
            return response()->json(['error' => 'invalid permis id'], 400);
        }
        if (!Permis::where(['id' => $permisId, 'sapeur_id' => $id])->exists()) {
            return response()->json(['error' => 'Permis non trouvé'], 404);
        }

        $permis = $this->business->updatePermis($id, $data);
        return response()->json(['data' => $permis]);
    }

    public function destroy(int $id, int $permisId)
    {
        if (!Sapeur::where('id', $id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!Permis::where(['id' => $permisId, 'sapeur_id' => $id])->exists()) {
            return response()->json(['error' => 'Permis non trouvé'], 404);
        }

        $this->business->removePermis($id, $permisId);
        return response()->json(['data' => 'success']);
    }
}
