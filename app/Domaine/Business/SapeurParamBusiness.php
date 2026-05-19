<?php


namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Cours;
use App\Models\CoursSapeur;
use App\Models\Fonction;
use App\Models\FonctionSapeur;
use App\Models\Grade;
use App\Models\GradeSapeur;
use App\Models\Groupe;

class SapeurParamBusiness
{

    public static function ajouterFonction($data)
    {
        $fonction = new Fonction();
        $fonction->fill($data);
        $fonction->save();
        return $fonction;
    }

    public static function modifierFonction($id, $data)
    {
        Fonction::whereId($id)->limit(1)->update($data);
        return Fonction::find($id);
    }

    public static function supprimerFonction($id): void
    {
        if (FonctionSapeur::where('fonction_id', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer cette fonction, celle-ci est attribuée à un sapeur.');
        }
        Fonction::whereId($id)->delete();
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
        Cours::whereId($id)->limit(1)->update($data);
        return Cours::find($id);
    }

    public static function supprimerCours($id)
    {
        if (CoursSapeur::where('cours_id', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce cours, celui-ci est attribué à un sapeur.');
        }
        Cours::whereId($id)->delete();
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
        Grade::whereId($id)->limit(1)->update($data);
        return Grade::find($id);
    }

    public static function supprimerGrade($id)
    {
        if (GradeSapeur::where('grade_id', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce grade, celui-ci est attribué à un sapeur.');
        }
        Grade::whereId($id)->delete();
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
        Groupe::whereId($id)->limit(1)->update($data);
        return Groupe::find($id);
    }

    public static function supprimerGroupe($id)
    {
        //TODO: Not implemented now
    }
}
