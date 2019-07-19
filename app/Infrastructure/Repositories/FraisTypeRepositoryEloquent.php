<?php


namespace App\Infrastructure\Repositories;


use App\Domaine\SPI\EcritureRepository;
use App\Domaine\SPI\FraisTypeRepository;
use App\Infrastructure\Models\FraisAnnuelType;
use stdClass;

class FraisTypeRepositoryEloquent implements FraisTypeRepository
{
    public function listeFraisAnnuelType()
    {
        $temp = $this;
        return FraisAnnuelType::all()
            ->map(function ($frais) use ($temp) {
                return $temp->convertFraisAnnuel($frais);
            })->toArray();
    }

    /**
     * @param $frais
     * @return StdClass|null
     */
    protected function convertFraisAnnuel($frais)
    {
        if ($frais == null) return null;

        $object = new StdClass();

        $object->id = $frais->id;
        $object->designation = $frais->designation;
        $object->quantite = $frais->quantite;
        $object->montant = $frais->montant;
        $object->fonction_id = $frais->fonction_id;
        $object->compte_id = $frais->compte_id;
        $object->ecriture_categorie_id = $frais->ecriture_categorie_id;
        $object->type_unite_id = $frais->type_unite_id;

        return $object;
    }

}
