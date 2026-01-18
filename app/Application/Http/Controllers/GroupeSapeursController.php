<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\OrganisationBusiness;
use Illuminate\Http\Request;

class GroupeSapeursController extends Controller
{
    protected $business;

    public function __construct(OrganisationBusiness $business)
    {
        $this->business = $business;
    }

    public function store(Request $request, $groupeId)
    {
        $data = $request->validate([
            '*' => 'integer|min:1',
        ]);

        $groupe = $this->business->modifierGroupeSapeurs($groupeId, $data);
        return response()->json(['data' => $groupe]);
    }
}
