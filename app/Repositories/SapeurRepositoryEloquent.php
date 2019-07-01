<?php


namespace App\Repositories;

use App\Contracts\SapeurRepository;
use App\Models\Sapeur;
use stdClass;

class SapeurRepositoryEloquent implements SapeurRepository
{
    private const SAPEUR_LIGHT_COLUMNS = ['id', 'nom', 'prenom', 'actif'];

    public function listeSapeurLight()
    {
        $temp = $this;
        return Sapeur::all(self::SAPEUR_LIGHT_COLUMNS)
            ->map(function ($sapeur) use ($temp) {
                return $temp->convertSapeurLight($sapeur);
            })->toArray();
    }

    public function getSapeurDetailsById($id, $with = [])
    {
        return $this->convertSapeur(Sapeur::with($with)->find($id), $with);
    }

    protected function convertSapeurLight($sapeur)
    {
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }

    protected function convertSapeur($sapeur, $with)
    {
        //TODO
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }

    protected function convertFonction($sapeur)
    {
        //TODO
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }

    protected function convertGrade($sapeur)
    {
        //TODO
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }

    protected function convertCours($sapeur)
    {
        //TODO
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }

    protected function convertTelephone($sapeur)
    {
        //TODO
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }

    protected function convertPromotion($sapeur)
    {
        //TODO
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }

    protected function convertMutation($sapeur)
    {
        //TODO
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }

    protected function convertPermis($sapeur)
    {
        //TODO
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }

    protected function convertGroupe($sapeur)
    {
        //TODO
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }
}
