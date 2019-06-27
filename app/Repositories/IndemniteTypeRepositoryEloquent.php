<?php


namespace App\Repositories;

use App\Contracts\IndemniteTypeRepository;
use App\Models\IndemniteExerciceType;
use App\Models\IndemniteInterventionType;
use stdClass;

class IndemniteTypeRepositoryEloquent implements IndemniteTypeRepository
{
    public function listeIndemniteExerciceType()
    {
//FIXME
    }

    public function listeIndemniteInterventionType()
    {
//FIXME
    }

    public function findIndemniteExerciceTypeById(int $id)
    {
        return $this->convertIndemniteExercice(IndemniteExerciceType::with('fonctions')->find($id));
    }

    public function findIndemniteInterventionTypeById(int $id)
    {
        return $this->convertIndemniteIntervention(IndemniteInterventionType::with('fonctions')->find($id));
    }

    /**
     * @param $indemnite
     * @return StdClass|null
     */
    protected function convertIndemniteExercice($indemnite)
    {
        if ($indemnite == null) return null;

        $object = new StdClass();

        $object->id = $indemnite->id;
        $object->designation = $indemnite->designation;
        $object->solde = $indemnite->solde;
        $object->indemnite = $indemnite->indemnite;
        $object->solde_min = $indemnite->solde_min;
        $object->solde_min_pour = $indemnite->solde_min_pour;
        $object->type_unite_id = $indemnite->type_unite_id;
        $object->compte_id = $indemnite->compte_id;
        $object->par_fonction = $indemnite->par_fonction;

        $indemnites = array();
        foreach ($indemnite->fonctions() as $indemnite) {
            array_push($indemnites, $this->convertIndemniteExerciceFonction($indemnite));
        }
        $object->fonctions = $indemnites;

        return $object;
    }

    /**
     * @param $indemnite
     * @return StdClass|null
     */
    protected function convertIndemniteExerciceFonction($indemnite)
    {
        if ($indemnite == null) return null;

        $object = new StdClass();

        $object->id = $indemnite->id;
        $object->fonction_id = $indemnite->fonction_id;
        $object->solde = $indemnite->solde;
        $object->indemnite = $indemnite->indemnite;
        $object->indemnite_int_id = $indemnite->indemnite_int_id;

        return $object;
    }

    /**
     * @param $intervention
     * @return StdClass|null
     */
    protected function convertIndemniteIntervention($intervention)
    {
        if ($intervention == null) return null;
        $object = new StdClass();

        $object->id = $intervention->id;

        $object->designation = $intervention->designation;
        $object->solde = $intervention->solde;
        $object->solde_min = $intervention->solde_min;
        $object->solde_min_pour = $intervention->solde_min_pour;
        $object->taux_weekend = $intervention->taux_weekend;
        $object->taux_nuit = $intervention->taux_nuit;
        $object->debut = $intervention->debut;
        $object->fin = $intervention->fin;
        $object->compte_id = $intervention->compte_id;
        $object->phase_id = $intervention->phase_id;
        $object->type_unite_id = $intervention->type_unite_id;
        $object->par_fonction = $intervention->par_fonction;

        $indemnites = array();
        foreach ($intervention->fonctions() as $indemnite) {
            array_push($indemnites, $this->convertIndemniteInterventionFonction($indemnite));
        }
        $object->fonctions = $indemnites;

        return $object;
    }

    /**
     * @param $indemnite
     * @return StdClass|null
     */
    protected function convertIndemniteInterventionFonction($indemnite)
    {
        if ($indemnite == null) return null;

        $object = new StdClass();
        $object->id = $indemnite->id;
        $object->fonction_id = $indemnite->fonction_id;
        $object->solde = $indemnite->solde;
        $object->indemnite_int_id = $indemnite->indemnite_int_id;

        return $object;
    }
}
