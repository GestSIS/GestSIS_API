<?php


namespace App\Domaine\Business;

use App\Infrastructure\Models\ExerciceCategorie;
use App\Infrastructure\Models\ExcuseType;
use App\Infrastructure\Models\HeureExerciceType;

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
        //TODO: Not implemented now
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
        //TODO: Not implemented now
    }

    public function ajouterHeuresExerciceType($data)
    {
        $type = new HeureExerciceType();
        $type->fill($data);
        $type->save();
        return $type;
    }

    public function modifierHeuresExerciceType($id, $data)
    {
        HeureExerciceType::where('id', $id)->limit(1)->update($data);
        return HeureExerciceType::find($id);
    }

    public function supprimerHeuresExerciceType($id)
    {
        HeureExerciceType::where('id', $id)->limit(1)->delete();
    }
}
