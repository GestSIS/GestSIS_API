<?php

namespace App\Infrastructure\Repositories;

use App\Domaine\SPI\FraisTypeRepository;
use App\Infrastructure\Models\FraisAnnuelType;
use stdClass;

class FraisTypeRepositoryEloquent implements FraisTypeRepository
{
    public function listeFraisAnnuelType()
    {
        $temp = $this;
        return FraisAnnuelType::with('fraisAnnuels')->get()
            ->map(function ($frais) use ($temp) {
                return $temp->convertFraisAnnuelType($frais);
            })->toArray();
    }

    /**
     * @param $frais
     * @return StdClass|null
     */
    protected function convertFraisAnnuelType($frais)
    {
        if ($frais == null) return null;

        $object = new StdClass();

        $object->id = $frais->id;
        $object->designation = $frais->designation;
        $object->cumulable = $frais->cumulable;
        $object->compte_id = $frais->compte_id;
        $object->ecriture_categorie_id = $frais->ecriture_categorie_id;

        $temp = $this;
        $object->fraisAnnuels = $frais->fraisAnnuels->map(function ($sap) use ($temp) {
            return $temp->convertFraisAnnuel($sap);
        })->toArray();

        return $object;
    }

    protected function convertFraisAnnuel($frais)
    {
        if ($frais == null) return null;

        $object = new StdClass();
        $object->id = $frais->id;

        $object->quantite = $frais->quantite;
        $object->montant = $frais->montant;
        $object->fonction_id = $frais->fonction_id;
        $object->type_unite_id = $frais->type_unite_id;
        $object->frais_annuel_type_id = $frais->frais_annuel_type_id;

        return $object;
    }
}
