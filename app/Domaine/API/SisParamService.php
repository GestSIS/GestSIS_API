<?php

namespace App\Domaine\API;

use App\Domaine\Business\SisParamBusiness;
use App\Infrastructure\Models\SisContact;
use App\Infrastructure\Models\SisParam;

class SisParamService
{
    protected $business;

    public function __construct(SisParamBusiness $business)
    {
        $this->business = $business;
    }

    public function params()
    {
        return SisParam::first();
    }

    public function updateParams($data)
    {
        return $this->business->updateParams($data);
    }

    public function getLogo($sisKey)
    {
        return $this->business->getLogo($sisKey);
    }

    public function updateLogo($sisKey, $file)
    {
        return $this->business->updateLogo($sisKey, $file);
    }

    public function ajouterLocalitesSis($data)
    {
        return $this->business->ajouterLocalitesSis($data);
    }

    public function supprimerLocalitesSis($ids)
    {
        return $this->business->supprimerLocalitesSis($ids);
    }

    public function contacts()
    {
        return SisContact::all();
    }

    public function ajouterContactSis($data)
    {
        return $this->business->ajouterContactSis($data);
    }

    public function supprimerContactSis(int $id)
    {
        return $this->business->supprimerContactSis($id);
    }
}
