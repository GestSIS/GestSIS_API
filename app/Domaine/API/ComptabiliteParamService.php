<?php

namespace App\Domaine\API;

use App\Domaine\Business\ComptabiliteParamBusiness;
use App\Domaine\SPI\EcritureRepository;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\SPI\IndemniteTypeRepository;
use App\Infrastructure\Models\Amende;
use App\Infrastructure\Models\Compte;
use App\Infrastructure\Models\EcritureCategorie;

class ComptabiliteParamService
{
    protected $ecritureRepo;
    protected $exerciceRepo;
    protected $indemniteRepo;
    protected $business;

    public function __construct(
        EcritureRepository $ecriture,
        ExerciceRepository $exercice,
        IndemniteTypeRepository $indemnite,
        ComptabiliteParamBusiness $business
    ) {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->indemniteRepo = $indemnite;
        $this->business = $business;
    }

    function amendes()
    {
        return Amende::all();
    }

    function updateAmendes($data)
    {
        return $this->business->updateAmendes($data);
    }

    function fraisIndemnitesTypes()
    {
        return array(
            "exercices" => $this->indemniteRepo->listeIndemniteExerciceType(),
            "interventions" => $this->indemniteRepo->listeIndemniteInterventionType(),
            "annuels" => $this->indemniteRepo->listeFraisIndemniteAnnuelType(),
        );
    }

    function categories()
    {
        return EcritureCategorie::all();
    }

    public function ajouterCategorie($data)
    {
        return $this->business->ajouterCategorie($data);
    }

    public function modifierCategorie($id, $data)
    {
        return $this->business->modifierCategorie($id, $data);
    }

    public function supprimerCategorie($id)
    {
        return $this->business->supprimerCategorie($id);
    }

    function fraisIndemnitesAnnuel()
    {
        return $this->indemniteRepo->listeFraisIndemniteAnnuelType();
    }

    public function ajouterFraisIndemniteAnnuel($data)
    {
        return $this->business->ajouterFraisIndemniteAnnuel($data);
    }

    public function modifierFraisIndemniteAnnuel($id, $data)
    {
        return $this->business->modifierFraisIndemniteAnnuel($id, $data);
    }

    public function supprimerFraisIndemniteAnnuel($id)
    {
        return $this->business->supprimerFraisIndemniteAnnuel($id);
    }

    public function ajouterFraisIndemniteAnnuelType($data)
    {
        return $this->business->ajouterFraisIndemniteAnnuelType($data);
    }

    public function modifierFraisIndemniteAnnuelType($id, $data)
    {
        return $this->business->modifierFraisIndemniteAnnuelType($id, $data);
    }

    public function supprimerFraisIndemniteAnnuelType($id)
    {
        return $this->business->supprimerFraisIndemniteAnnuelType($id);
    }

    function indemnitesExercice()
    {
        return $this->indemniteRepo->listeIndemniteExerciceType();
    }

    public function ajouterIndemniteExercice($data)
    {
        return $this->business->ajouterIndemniteExercice($data);
    }

    public function modifierIndemniteExercice($id, $data)
    {
        return $this->business->modifierIndemniteExercice($id, $data);
    }

    public function supprimerIndemniteExercice($id)
    {
        return $this->business->supprimerIndemniteExercice($id);
    }

    function indemnitesIntervention()
    {
        return $this->indemniteRepo->listeIndemniteInterventionType();
    }

    public function ajouterIndemniteIntervention($data)
    {
        return $this->business->ajouterIndemniteIntervention($data);
    }

    public function modifierIndemniteIntervention($id, $data)
    {
        return $this->business->modifierIndemniteIntervention($id, $data);
    }

    public function supprimerIndemniteIntervention($id)
    {
        return $this->business->supprimerIndemniteIntervention($id);
    }

    public function comptes()
    {
        return Compte::all();
    }

    public function ajouterCompte($data)
    {
        return $this->business->ajouterCompte($data);
    }

    public function modifierCompte($id, $data)
    {
        return $this->business->modifierCompte($id, $data);
    }

    public function supprimerCompte($id)
    {
        return $this->business->supprimerCompte($id);
    }
}
