<?php

namespace App\Application\Http\Controllers;

use App\Models\LocaliteSis;
use App\Models\SisContact;
use App\Models\SisParam;
use App\Support\Sis;

class AdminController extends Controller
{
    public function sisContacts()
    {
        return response()->json(['data' => Sis::each(fn () => SisContact::all())]);
    }

    public function sisParams()
    {
        return response()->json(['data' => Sis::each(fn () => SisParam::with(['sapeur', 'localite'])->first())]);
    }

    public function sisLocalites()
    {
        return response()->json(['data' => Sis::each(fn () => LocaliteSis::with('localite')->get())]);
    }
}
