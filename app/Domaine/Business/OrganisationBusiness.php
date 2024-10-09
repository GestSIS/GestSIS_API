<?php


namespace App\Domaine\Business;

use App\Infrastructure\Models\Groupe;
use App\Infrastructure\Models\GroupeSapeur;

class OrganisationBusiness
{

    public function ajouterGroupe($data)
    {
        $data['tri'] = Groupe::max('tri') + 1;

        $groupe = new Groupe();
        $groupe->fill($data);
        $groupe->save();

        return Groupe::with('sapeurIds')->find($groupe->id);
    }

    public function modifierGroupe($groupeId, $data)
    {
        // Chargement des groupes
        $groupes = Groupe::get();
        $groupesMap = [];
        foreach ($groupes as $groupe) {
            $groupesMap[$groupe->id] = $groupe->parent_id;
        }

        // Controle qu'il n'y ait pas de loop dans la hierarchie des groupes
        $pereId = array_key_exists('parent_id', $data) ? $data['parent_id'] : null;
        $visited = [];
        while (!is_null($pereId)) {
            if (in_array($pereId, $visited) || $pereId == $groupeId) {
                return response()->json(["message" => "Groupe parent invalide, création d'une boucle"], 501);
            }
            $visited[] = $pereId;
            $pereId = $groupesMap[$pereId];
        }

        Groupe::where('id', $groupeId)->limit(1)->update($data);
        return Groupe::with('sapeurIds')->find($groupeId);
    }

    public function supprimerGroupe($groupeId)
    {
        Groupe::where('id', $groupeId)->limit(1)->delete();
    }

    public function modifierGroupeSapeurs($groupeId, $sapeurIds)
    {
        $groupe = Groupe::find($groupeId);
        $groupe->sapeurs()->sync($sapeurIds);
        return GroupeSapeur::where('groupe_id', $groupeId)->get();
    }
}
