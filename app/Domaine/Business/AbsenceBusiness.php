<?php


namespace App\Domaine\Business;

use App\Infrastructure\Models\Absence;

class AbsenceBusiness
{

    public function ajouterAbsence($data)
    {
        $absence = new Absence();
        $absence->fill($data);
        $absence->save();
        return $absence;
    }

    public function modifierAbsence($absenceId, $data)
    {
        // Chargement des absences
        Absence::where('id', $absenceId)->limit(1)->update($data);
        return Absence::find($absenceId);
    }

    public function supprimerAbsence($absenceId)
    {
        Absence::where('id', $absenceId)->limit(1)->delete();
    }
}
