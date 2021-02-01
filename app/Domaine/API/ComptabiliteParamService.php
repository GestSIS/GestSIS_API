<?php

namespace App\Domaine\API;

use App\Domaine\Business\ComptabiliteParamBusiness;
use App\Domaine\SPI\CompteRepository;
use App\Domaine\SPI\EcritureRepository;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\SPI\FraisTypeRepository;
use App\Domaine\SPI\IndemniteTypeRepository;

class ComptabiliteParamService
{
    protected $ecritureRepo;
    protected $exerciceRepo;
    protected $indemniteRepo;
    protected $fraisRepo;
    protected $compteRepo;
    protected $business;

    public function __construct(
        EcritureRepository $ecriture,
        ExerciceRepository $exercice,
        IndemniteTypeRepository $indemnite,
        FraisTypeRepository $frais,
        CompteRepository $comptes,
        ComptabiliteParamBusiness $business)
    {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->indemniteRepo = $indemnite;
        $this->fraisRepo = $frais;
        $this->compteRepo = $comptes;
        $this->business = $business;
    }
    
    function indemnitesTypes()
    {
        return array(
            "exercices" => $this->indemniteRepo->listeIndemniteExerciceType(),
            "interventions" => $this->indemniteRepo->listeIndemniteInterventionType(),
            "annuels" => $this->indemniteRepo->listeIndemniteAnnuelType(),
        );
    }

    function fraisTypes()
    {
        return array(
            "annuels" => $this->fraisRepo->listeFraisAnnuelType()
        );
    }

    function indemnitesAnnuel()
    {
        return $this->indemniteRepo->listeIndemniteExerciceType();
    }

    public function ajouterIndemniteAnnuel($data)
    {
        return $this->business->ajouterIndemniteAnnuel($data);
    }

    public function modifierIndemniteAnnuel($id, $data)
    {
        return $this->business->modifierIndemniteAnnuel($id, $data);
    }

    public function supprimerIndemniteAnnuel($id)
    {
        return $this->business->supprimerIndemniteAnnuel($id);
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

    function fraisAnnuel()
    {
        return $this->fraisRepo->listeFraisAnnuelType();
    }

    public function ajouterFraisAnnuel($data)
    {
        return $this->business->ajouterFraisAnnuel($data);
    }

    public function modifierFraisAnnuel($id, $data)
    {
        return $this->business->modifierFraisAnnuel($id, $data);
    }

    public function supprimerFraisAnnuel($id)
    {
        return $this->business->supprimerFraisAnnuel($id);
    }

    public function comptes()
    {
        return $this->compteRepo->listComptes();
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