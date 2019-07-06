<?php


namespace App\Repositories;


use App\Contracts\CompteRepository;
use App\Models\Compte;
use stdClass;

class CompteRepositoryEloquent implements CompteRepository
{
    public function listComptes()
    {
        $temp = $this;
        return Compte::all()
            ->map(function ($compte) use ($temp) {
                return $temp->convertCompte($compte);
            })->toArray();
    }

    /**
     * @param $compte
     * @return StdClass|null
     */
    protected function convertCompte($compte)
    {
        if ($compte == null) return null;

        $object = new StdClass();

        $object->id = $compte->id;
        $object->numero = $compte->numero;
        $object->designation = $compte->designation;

        return $object;
    }

}
