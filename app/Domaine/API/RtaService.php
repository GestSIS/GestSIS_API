<?php


namespace App\Domaine\API;


use App\Domaine\Business\SapeurBusiness;
use App\Infrastructure\Models\ReferenceRta;
use Carbon\Carbon;

class RtaService
{
    protected $business;

    public function __construct(SapeurBusiness $business)
    {
        $this->business = $business;
    }

    public function getReference()
    {
        return ReferenceRta::firstOrNew([
            'date' => Carbon::now(),
            'data' => '[]'
        ]);
    }

    public function setReference($data)
    {
        return ReferenceRta::updateOrCreate([], [
            'date' => Carbon::now(),
            'data' => $data
        ]);
    }
}
