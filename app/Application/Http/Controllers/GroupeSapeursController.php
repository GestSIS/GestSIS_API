<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\OrganisationBusiness;
use Illuminate\Http\Request;

class GroupeSapeursController extends Controller
{

    public function store(Request $request, $groupeId)
    {
        $data = $request->validate([
            '*' => 'integer|min:1',
        ]);

        $groupe = OrganisationBusiness::modifierGroupeSapeurs($groupeId, $data);
        return response()->json(['data' => $groupe]);
    }
}
