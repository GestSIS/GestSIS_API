<?php

namespace App\Domaine\API;

use App\Domaine\Business\ControleMedicalBusiness;
use App\Domaine\SPI\ControleMedicalRepository;
use App\Infrastructure\Models\Sapeur;

class EmailService
{
    protected $repository;
    protected $business;

    public function __construct(ControleMedicalRepository $repository, ControleMedicalBusiness $business)
    {
        $this->repository = $repository;
        $this->business = $business;
    }
    
    public function checkEmail($email)
    {
        return Sapeur::where('email', '=', $email)->exists();
    }
}
