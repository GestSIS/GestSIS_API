<?php

namespace App\Http\Controllers;

use App\Services\ComptabiliteService;
use Illuminate\Http\Response;
use PDF;

class CompteController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return response()->json(['data' => $this->service->getComptes()]);
    }

    public function generatePdf()
    {
        $exerciceComptableId = 1;



//        $pdf->loadHTML('<h1>Test</h1>');
        $pdf = PDF::loadView('decomptes-sapeurs', ["test"=>"bastien"]);
        return $pdf->download('invoice.pdf');
//        return $pdf->stream();
    }
}
