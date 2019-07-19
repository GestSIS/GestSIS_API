<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\ExerciceCategorie;
use Illuminate\Http\Response;

class ExerciceCategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $exerciceCategories = ExerciceCategorie::all();

        return response()->json(['data' => $exerciceCategories]);
    }
}
