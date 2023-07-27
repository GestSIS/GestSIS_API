<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SisParamService;
use Illuminate\Http\Request;

class SisContactController extends Controller
{
    protected $service;

    public function __construct(SisParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $params = $this->service->contacts();

        return response()->json(['data' => $params]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'liste' => 'required|string',
        ]);

        $contact = $this->service->ajouterContactSis($data);

        return response()->json(['data' => $contact]);
    }

    public function destroy(int $contactId)
    {
        $this->service->supprimerContactSis($contactId);
        return response()->json(['data' => 'ok']);
    }
}
