<?php


namespace App\Domaine\Business;

use App\Models\Groupe;
use App\Models\GroupeSapeur;

class OrganisationBusiness
{

    public static function ajouterGroupe($data)
    {
        $data['tri'] = Groupe::max('tri') + 1;

        $groupe = new Groupe();
        $groupe->fill($data);
        $groupe->save();

        return Groupe::with('sapeurIds')->find($groupe->id);
    }

    public static function modifierGroupe($groupeId, $data)
    {
        // Chargement des groupes
        $groupes = Groupe::get();
        $groupesMap = $groupes->mapWithKeys(fn($groupe) => [$groupe->id => $groupe->parent_id]);

        // Controle qu'il n'y ait pas de loop dans la hierarchie des groupes
        $pereId = array_key_exists('parent_id', $data) ? $data['parent_id'] : null;
        $visited = collect();
        while ($pereId !== null) {
            if ($visited->contains($pereId) || $pereId === $groupeId) {
                return response()->json(["message" => "Groupe parent invalide, création d'une boucle"], 501);
            }
            $visited->push($pereId);
            $pereId = $groupesMap[$pereId];
        }

        Groupe::whereId($groupeId)->limit(1)->update($data);
        return Groupe::with('sapeurIds')->find($groupeId);
    }

    public static function supprimerGroupe($groupeId)
    {
        Groupe::whereId($groupeId)->limit(1)->delete();
    }

    public static function modifierGroupeSapeurs($groupeId, $sapeurIds)
    {
        $groupe = Groupe::find($groupeId);
        $groupe->sapeurs()->sync($sapeurIds);
        return GroupeSapeur::where('groupe_id', $groupeId)->get();
    }
}
