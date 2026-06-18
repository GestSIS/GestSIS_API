<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Domaine\Exceptions\InvalidActionException;
use App\Models\Intervention;
use App\Models\InterventionMateriel;
use App\Models\InterventionTraitement;
use App\Models\Materiel;
use App\Models\MissionType;
use App\Models\StatFederal;
use App\Models\StatIntervention;
use App\Models\Telephone;
use App\Models\TypeIntervention;

class InterventionParamBusiness
{
    public static function ajouterStat($data)
    {
        $stat = new StatIntervention();
        $stat->fill($data);
        $stat->save();
        return $stat;
    }

    public static function modifierStat($id, $data)
    {
        StatIntervention::whereId($id)->limit(1)->update($data);
        return StatIntervention::find($id);
    }

    public function supprimerStat($id)
    {
        if (TypeIntervention::where('stat_intervention_id', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer cette catégorie statististique, celle-ci est liée à un type d\'intervention.');
        }
        StatIntervention::whereId($id)->delete();
    }

    public static function ajouterStatFederal($data)
    {
        $stat = new StatFederal();
        $stat->fill($data);
        $stat->save();
        return $stat;
    }

    public static function modifierStatFederal($id, $data)
    {
        StatFederal::whereId($id)->limit(1)->update($data);
        return StatFederal::find($id);
    }

    public static function supprimerStatFederal($id)
    {
        //TODO: Not implemented now
    }

    public static function ajouterType($data)
    {
        $type = new TypeIntervention();
        $type->fill($data);
        $type->save();
        return $type;
    }

    public static function modifierType($id, $data)
    {
        TypeIntervention::whereId($id)->limit(1)->update($data);
        return TypeIntervention::find($id);
    }

    public static function supprimerType($id)
    {
        if (Intervention::where('type_intervention_id', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce type d\'intervention, celui-ci est utilisé dans une intervention.');
        }
        TypeIntervention::whereId($id)->delete();
    }

    public static function ajouterMission($data)
    {
        $mission = new MissionType();
        $mission->fill($data);
        $mission->save();
        return $mission;
    }

    public static function modifierMission($id, $data)
    {
        MissionType::whereId($id)->limit(1)->update($data);
        return MissionType::find($id);
    }

    public static function supprimerMission($id)
    {
        MissionType::whereId($id)->delete();
    }

    public static function ajouterTelephone($data)
    {
        $telephone = new Telephone();
        $telephone->fill($data);
        $telephone->save();
        return $telephone;
    }

    public static function modifierTelephone($id, $data)
    {
        Telephone::whereId($id)->limit(1)->update($data);
        return Telephone::find($id);
    }

    public static function supprimerTelephone($id)
    {
        Telephone::whereId($id)->delete();
    }

    public static function ajouterMateriel($data)
    {
        if (!array_key_exists('type_unite_id', $data) || (int) $data['type_unite_id'] === 0) {
            $data['type_unite_id'] = null;
        }
        $materiel = new Materiel();
        $materiel->fill($data);
        $materiel->save();
        return $materiel;
    }

    public static function modifierMateriel($id, $data)
    {
        if (!array_key_exists('type_unite_id', $data) || (int) $data['type_unite_id'] === 0) {
            $data['type_unite_id'] = null;
        }
        Materiel::whereId($id)->limit(1)->update($data);
        return Materiel::find($id);
    }

    public static function supprimerMateriel($id)
    {
        if (InterventionMateriel::where('materiel_id', $id)->exists()) {
            throw new InvalidActionException([], 'Impossible de supprimer ce matériel, celui-ci est utilisé dans une intervention.');
        }
        Materiel::whereId($id)->delete();
    }

    public static function ajouterTraitement($data)
    {
        $categorie = new InterventionTraitement();
        $categorie->fill($data);
        $categorie->save();
        return $categorie;
    }

    public static function modifierTraitement($id, $data)
    {
        InterventionTraitement::whereId($id)->limit(1)->update($data);
        return InterventionTraitement::find($id);
    }

    public static function supprimerTraitement($id)
    {
        if (Intervention::where('intervention_traitement_id', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce traitement, celui-ci est utilisé dans un exercice.');
        }
        InterventionTraitement::whereId($id)->delete();
    }
}
