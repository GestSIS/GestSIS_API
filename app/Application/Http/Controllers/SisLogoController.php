<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SisParamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SisLogoController extends Controller
{
    protected $service;

    public function __construct(SisParamService $service)
    {
        $this->service = $service;
    }

    public function show($sisKey)
    {
        $path = $this->service->getLogo($sisKey);

        if ($path == null) {
            return null;
        }
        return Storage::download($path);
    }

    public function store(Request $request)
    {
        if (!$request->hasFile('logo') || !$request->file('logo')->isValid()) {
            return response()->json(['error' => 'Logo manquant']);
        }

        $file = $request->file('logo');
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        $justificatif = $this->service->updateLogo($sisKey, $file);

        return response()->json(['data' => $justificatif]);
    }
}
