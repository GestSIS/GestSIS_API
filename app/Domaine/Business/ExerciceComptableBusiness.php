<?php

namespace App\Domaine\Business;

use App\Models\ExerciceComptable;


class ExerciceComptableBusiness
{

    public static function creerExerciceComptable($data): ExerciceComptable
    {
        $exercice = new ExerciceComptable();
        $exercice->fill($data);
        $exercice->boucle = false;
        $exercice->save();
        return $exercice;
    }

    public static function modifierExerciceComptable($id, $data): ?ExerciceComptable
    {
        ExerciceComptable::whereId($id)->limit(1)->update($data);
        return ExerciceComptable::find($id);
    }

    public static function supprimerExerciceComptable($id): void
    {
        //TODO: Not implemented now
    }

    public static function cloturerExerciceComptable($id): ?ExerciceComptable
    {
        ExerciceComptable::whereId($id)->limit(1)->update(['boucle' => true]);
        return ExerciceComptable::find($id);
    }
}