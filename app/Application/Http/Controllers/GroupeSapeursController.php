<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\Groupe;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\Request;

class GroupeSapeursController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groupes = Groupe::with('sapeurs')->get();

        return response()->json(['data' => $groupes]);
    }
}
