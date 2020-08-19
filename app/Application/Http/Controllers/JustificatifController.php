<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ControleMedicalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JustificatifController extends Controller
{
    protected $service;

    public function __construct(ControleMedicalService $service)
    {
        $this->service = $service;
    }
    
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $controleId, int $id)
    {
        //TODO: Return a file
        $logicalname = $this->service->getJustificatif($controleId, $id);
        
        $headers = array(
            'Content-Type: application/pdf',
            'Cache-Control: no-cache private',
            'Content-Description: File Transfer',
            'Content-Disposition: attachment; filename=abc.pdf',
            'Content-Transfer-Encoding: binary'
        );
        return Storage::download($logicalname, 'abc.pdf', $headers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request, int $id)
    {
        if (!$request->hasFile('justificatif') || !$request->file('justificatif')->isValid())
        {
            return response()->json(['error' => 'Fichier justificatif manquant']);    
        }

        $file = $request->file('justificatif');

        $justificatif = $this->service->addJustificatif($id, $file);

        return response()->json(['data' => $justificatif]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $controleId, int $id)
    {
        $this->service->removeJustificatif($controleId, $id);

        return response()->json(['data' => "success"]);
    }
}
