<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\Groupe;
use App\Domaine\API\GroupeService;
use Illuminate\Http\Request;

class GroupeSapeursController extends Controller
{
    protected $service;

    public function __construct(GroupeService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request, $groupeId)
    {
        $data = $request->validate([
            '*' => 'integer|min:1',
        ]);

        $groupe = $this->service->modifierGroupeSapeurs($groupeId, $data);
        return response()->json(['data' => $groupe]);
    }
}
