<?php


namespace App\Domaine\Business;

use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\Cours;
use App\Infrastructure\Models\Fonction;
use App\Infrastructure\Models\Grade;
use App\Infrastructure\Models\Groupe;

class SapeurParamBusiness
{

    protected $repository;

    public function __construct(SapeurRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function ajouterFonction($data)
    {
        $fonction = new Fonction();
        $fonction->fill($data);
        $fonction->save();
        return $fonction;
    }

    public static function modifierFonction($id, $data)
    {
        Fonction::where('id', $id)->limit(1)->update($data);
        return Fonction::find($id);
    }

    public static function supprimerFonction($id)
    {
        //TODO: Not implemented now
    }

    public static function ajouterCours($data)
    {
        $cours = new Cours();
        $cours->fill($data);
        $cours->save();
        return $cours;
    }

    public static function modifierCours($id, $data)
    {
        Cours::where('id', $id)->limit(1)->update($data);
        return Cours::find($id);
    }

    public static function supprimerCours($id)
    {
        //TODO: Not implemented now
    }

    public static function ajouterGrade($data)
    {
        $grade = new Grade();
        $grade->fill($data);
        $grade->save();
        return $grade;
    }

    public static function modifierGrade($id, $data)
    {
        Grade::where('id', $id)->limit(1)->update($data);
        return Grade::find($id);
    }

    public static function supprimerGrade($id)
    {
        //TODO: Not implemented now
    }

    public static function ajouterGroupe($data)
    {
        $groupe = new Groupe();
        $groupe->fill($data);
        $groupe->save();
        return $groupe;
    }

    public static function modifierGroupe($id, $data)
    {
        Groupe::where('id', $id)->limit(1)->update($data);
        return Groupe::find($id);
    }

    public static function supprimerGroupe($id)
    {
        //TODO: Not implemented now
    }
}
