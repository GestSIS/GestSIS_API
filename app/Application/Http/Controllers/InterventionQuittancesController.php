<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Infrastructure\Models\Quittance;
use Illuminate\Http\Request;

class InterventionQuittancesController extends Controller
{
    protected $business;

    public function __construct(InterventionBusiness $business)
    {
        $this->business = $business;
    }

    public function index($intervention_id)
    {
        return response()->json(['data' => Quittance::where('intervention_id', $intervention_id)->get()]);
    }

    public function store(Request $request, int $intervention_id)
    {
        $data = $request->validate(['quittances.*' => 'required|integer|min:1']);
        $this->business->addQuittances($intervention_id, $data['quittances']);
        return response()->json(['data' => Quittance::where('intervention_id', $intervention_id)->get()]);
    }

    public function destroy(Request $request, int $intervention_id)
    {
        $data = $request->validate(['quittances.*' => 'required|integer|min:1']);
        $this->business->removeQuittances($intervention_id, $data['quittances']);
        return response()->json(['data' => 'success']);
    }
}
