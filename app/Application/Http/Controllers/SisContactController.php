<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SisParamBusiness;
use App\Infrastructure\Models\SisContact;
use Illuminate\Http\Request;

class SisContactController extends Controller
{
    public function index()
    {
        $params = SisContact::all();

        return response()->json(['data' => $params]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'liste' => 'required|string',
        ]);

        $contact = SisParamBusiness::ajouterContactSis($data);

        return response()->json(['data' => $contact]);
    }

    public function destroy(int $contactId)
    {
        SisParamBusiness::supprimerContactSis($contactId);
        return response()->json(['data' => 'ok']);
    }
}
