<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\ExerciceComptable;


class ExerciceComptableBusiness
{

    public static function creerExerciceComptable($data)
    {        
        $exercice = new ExerciceComptable();
        $exercice->fill($data);
        $exercice->boucle = false;
        $exercice->save();
        return $exercice;
    }

    public static function modifierExerciceComptable($id, $data)
    {
        ExerciceComptable::where('id', $id)->limit(1)->update($data);
        return ExerciceComptable::find($id);
    }

    public static function supprimerExerciceComptable($id)
    {
        //TODO: Not implemented now
    }

    public static function cloturerExerciceComptable($id)
    {
        ExerciceComptable::where('id', $id)->limit(1)->update(['boucle' => 1]);
        return ExerciceComptable::find($id);
    }
}