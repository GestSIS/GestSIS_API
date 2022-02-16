<?php


namespace App\Infrastructure\Repositories;

use App\Domaine\SPI\IndemniteTypeRepository;
use App\Infrastructure\Models\IndemniteAnnuelType;
use App\Infrastructure\Models\IndemniteExerciceType;
use App\Infrastructure\Models\IndemniteInterventionType;
use stdClass;

class IndemniteTypeRepositoryEloquent implements IndemniteTypeRepository
{
    public function listeIndemniteExerciceType()
    {
        $temp = $this;
        return IndemniteExerciceType::with('fonctions')
            ->get()
            ->map(function ($indemnite) use ($temp) {
                return $temp->convertIndemniteExercice($indemnite);
            })->toArray();
    }

    public function listeIndemniteInterventionType()
    {
        return IndemniteInterventionType::with('fonctions')
            ->get()
            ->toArray();
    }

    public function listeIndemniteAnnuelType()
    {
        $temp = $this;
        return IndemniteAnnuelType::with('indemniteAnnuels')->get()
            ->map(function ($indemnite) use ($temp) {
                return $temp->convertIndemniteAnnuelType($indemnite);
            })->toArray();
    }

    public function findIndemniteExerciceTypeById(int $id)
    {
        return $this->convertIndemniteExercice(IndemniteExerciceType::with('fonctions')->find($id));
    }

    public function findIndemniteInterventionTypeById(int $id)
    {
        return IndemniteInterventionType::with('fonctions')->find($id);
    }

    /**
     * @param $indemnite
     * @return StdClass|null
     */
    protected function convertIndemniteAnnuelType($indemnite)
    {
        if ($indemnite == null) return null;

        $object = new StdClass();

        $object->id = $indemnite->id;
        $object->designation = $indemnite->designation;
        $object->cumulable = $indemnite->cumulable;
        $object->compte_id = $indemnite->compte_id;
        $object->ecriture_categorie_id = $indemnite->ecriture_categorie_id;

        $temp = $this;
        $object->indemniteAnnuels = $indemnite->indemniteAnnuels->map(function ($ind) use ($temp) {
            return $temp->convertIndemniteAnnuel($ind);
        })->toArray();

        return $object;
    }

    protected function convertIndemniteAnnuel($indemnite)
    {
        if ($indemnite == null) return null;

        $object = new StdClass();
        $object->id = $indemnite->id;

        $object->quantite = $indemnite->quantite;
        $object->montant = $indemnite->montant;
        $object->fonction_id = $indemnite->fonction_id;
        $object->type_unite_id = $indemnite->type_unite_id;
        $object->indemnite_annuel_type_id = $indemnite->indemnite_annuel_type_id;

        return $object;
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
        $object->ecriture_categorie_id = $indemnite->ecriture_categorie_id;

        $indemnites = array();
        foreach ($indemnite->fonctions as $indemnite) {
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
}
