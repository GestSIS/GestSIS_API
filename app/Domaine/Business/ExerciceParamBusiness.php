<?php


namespace App\Domaine\Business;

use App\Domaine\Exceptions\InvalidActionException;
use App\Models\ExerciceCategorie;
use App\Models\ExcuseType;
use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\HeureExerciceType;

class ExerciceParamBusiness
{
    public static function ajouterCategorie($data)
    {
        $categorie = new ExerciceCategorie();
        $categorie->fill($data);
        $categorie->save();
        return $categorie;
    }

    public static function modifierCategorie($id, $data)
    {
        ExerciceCategorie::where('id', $id)->limit(1)->update($data);
        return ExerciceCategorie::find($id);
    }

    public static function supprimerCategorie($id)
    {
        if (Exercice::where('exercice_categorie_id', '=', $id)->exists()) {
            throw new InvalidActionException(message: 'Impossible de supprimer cette catégorie, celle-ci est utilisée dans un exercice.');
        }
        ExerciceCategorie::where('id', $id)->delete();
    }

    public static function ajouterExcuseType($data)
    {
        $excuse = new ExcuseType();
        $excuse->fill($data);
        $excuse->save();
        return $excuse;
    }

    public static function modifierExcuseType($id, $data)
    {
        ExcuseType::where('id', $id)->limit(1)->update($data);
        return ExcuseType::find($id);
    }

    public static function supprimerExcuseType($id)
    {
        if (ExerciceSapeur::where('excuse_type_id', '=', $id)->exists()) {
            throw new InvalidActionException(message: 'Impossible de supprimer cette excuse, celle-ci est utilisée dans un exercice.');
        }
        ExcuseType::where('id', $id)->delete();
    }

    public static function ajouterHeureExerciceType($data)
    {
        $type = new HeureExerciceType();
        $type->fill($data);
        $type->save();
        return $type;
    }

    public static function modifierHeureExerciceType($id, $data)
    {
        HeureExerciceType::where('id', $id)->limit(1)->update($data);
        return HeureExerciceType::find($id);
    }

    public static function supprimerHeureExerciceType($id)
    {
        HeureExerciceType::where('id', $id)->limit(1)->delete();
    }
}
