<?php


namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Absence;
use Carbon\Carbon;

class AbsenceBusiness
{

    private static function checkOverlap($absenceId, $sapeurId, $absence): bool
    {
        return Absence::where([
            ['id', '<>', $absenceId],
            ['sapeur_id', '=', $sapeurId],
            ['debut', '<=', $absence['fin']],
            ['fin', '>=', $absence['debut']]
        ])->exists();
    }

    public static function ajouterAbsence($data)
    {
        if (Carbon::parse($data['debut'])->gt(Carbon::parse($data['fin']))) {
            throw new ArrayException(['fin' => 'Invalide'], 'La date de fin ne peut être antérieur à la date de début !');
        }

        if (self::checkOverlap(Null, $data['sapeur_id'], $data)) {
            throw new ArrayException(['debut' => 'Invalide', 'fin' => 'Invalide'], 'Cette absence chevauche une autre absence');
        }
        $absence = new Absence();
        $absence->fill($data);
        $absence->sapeur_id = $data['sapeur_id'];
        $absence->save();
        return $absence;
    }

    public static function modifierAbsence($absenceId, $data)
    {
        if (Carbon::parse($data['debut'])->gt(Carbon::parse($data['fin']))) {
            throw new ArrayException(['fin' => 'Invalide'], 'La date de fin ne peut être antérieur à la date de début !');
        }

        $absence = Absence::find($absenceId);
        if (!$absence) {
            throw new ArrayException([], 'Absence inexistante');
        }

        // Absence overlapping
        if (self::checkOverlap($absenceId, $absence->sapeur_id, $data)) {
            throw new ArrayException(['debut' => 'Invalide', 'fin' => 'Invalide'], 'Cette absence chevauche une autre absence');
        }
        // Chargement des absences
        Absence::where('id', $absenceId)->limit(1)->update($data);
        return Absence::find($absenceId);
    }

    public static function supprimerAbsence($absenceId)
    {
        Absence::where('id', $absenceId)->limit(1)->delete();
    }
}
