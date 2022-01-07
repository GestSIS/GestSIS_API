<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\InterventionMateriel;
use App\Infrastructure\Models\InterventionTraitement;
use App\Infrastructure\Models\InterventionVehicule;
use App\Infrastructure\Models\Materiel;
use App\Infrastructure\Models\MissionType;
use App\Infrastructure\Models\StatFederal;
use App\Infrastructure\Models\StatIntervention;
use App\Infrastructure\Models\Telephone;
use App\Infrastructure\Models\TypeIntervention;
use App\Infrastructure\Models\Vehicule;

class InterventionParamBusiness
{
    public function ajouterStat($data)
    {
        $stat = new StatIntervention();
        $stat->fill($data);
        $stat->save();
        return $stat;
    }

    public function modifierStat($id, $data)
    {
        StatIntervention::where('id', $id)->limit(1)->update($data);
        return StatIntervention::find($id);
    }

    public function supprimerStat($id)
    {
        if (TypeIntervention::where('stat_intervention_id', '=', $id)->exists()) {
            throw new ArrayException(['message' => 'Impossible de supprimer cette catégorie statististique, celle-ci est liée à un type d\'intervention.']);
        }
        StatIntervention::where('id', $id)->delete();
    }

    public function ajouterStatFederal($data)
    {
        $stat = new StatFederal();
        $stat->fill($data);
        $stat->save();
        return $stat;
    }

    public function modifierStatFederal($id, $data)
    {
        StatFederal::where('id', $id)->limit(1)->update($data);
        return StatFederal::find($id);
    }

    public static function supprimerStatFederal($id)
    {
        //TODO: Not implemented now
    }

    public function ajouterType($data)
    {
        $type = new TypeIntervention();
        $type->fill($data);
        $type->save();
        return $type;
    }

    public function modifierType($id, $data)
    {
        TypeIntervention::where('id', $id)->limit(1)->update($data);
        return TypeIntervention::find($id);
    }

    public static function supprimerType($id)
    {
        if (Intervention::where('type_intervention_id', '=', $id)->exists()) {
            throw new ArrayException(['message' => 'Impossible de supprimer ce type d\'intervention, celui-ci est utilisé dans une intervention.']);
        }
        TypeIntervention::where('id', $id)->delete();
    }

    public function ajouterMission($data)
    {
        $mission = new MissionType();
        $mission->fill($data);
        $mission->save();
        return $mission;
    }

    public function modifierMission($id, $data)
    {
        MissionType::where('id', $id)->limit(1)->update($data);
        return MissionType::find($id);
    }

    public static function supprimerMission($id)
    {
        MissionType::where('id', $id)->delete();
    }

    public function ajouterTelephone($data)
    {
        $telephone = new Telephone();
        $telephone->fill($data);
        $telephone->save();
        return $telephone;
    }

    public function modifierTelephone($id, $data)
    {
        Telephone::where('id', $id)->limit(1)->update($data);
        return Telephone::find($id);
    }

    public static function supprimerTelephone($id)
    {
        Telephone::where('id', $id)->delete();
    }

    public function ajouterVehicule($data)
    {
        if (!array_key_exists('type_unite_id', $data) || $data['type_unite_id'] == "0") {
            $data['type_unite_id'] = null;
        }
        $vehicule = new Vehicule();
        $vehicule->fill($data);
        $vehicule->save();
        return $vehicule;
    }

    public function modifierVehicule($id, $data)
    {
        if (!array_key_exists('type_unite_id', $data) || $data['type_unite_id'] == "0") {
            $data['type_unite_id'] = null;
        }
        Vehicule::where('id', $id)->limit(1)->update($data);
        return Vehicule::find($id);
    }

    public static function supprimerVehicule($id)
    {
        if (InterventionVehicule::where('vehicule_id', '=', $id)->exists()) {
            throw new ArrayException(['message' => 'Impossible de supprimer ce véhicule, celui-ci est utilisé dans une intervention.']);
        }
        Vehicule::where('id', $id)->delete();
    }

    public function ajouterMateriel($data)
    {
        if (!array_key_exists('type_unite_id', $data) || $data['type_unite_id'] == "0") {
            $data['type_unite_id'] = null;
        }
        $materiel = new Materiel();
        $materiel->fill($data);
        $materiel->save();
        return $materiel;
    }

    public function modifierMateriel($id, $data)
    {
        if (!array_key_exists('type_unite_id', $data) || $data['type_unite_id'] == "0") {
            $data['type_unite_id'] = null;
        }
        Materiel::where('id', $id)->limit(1)->update($data);
        return Materiel::find($id);
    }

    public static function supprimerMateriel($id)
    {
        if (InterventionMateriel::where('materiel_id', '=', $id)->exists()) {
            throw new ArrayException(['message' => 'Impossible de supprimer ce matériel, celui-ci est utilisé dans une intervention.']);
        }
        Materiel::where('id', $id)->delete();
    }

    public function ajouterTraitement($data)
    {
        $categorie = new InterventionTraitement();
        $categorie->fill($data);
        $categorie->save();
        return $categorie;
    }

    public function modifierTraitement($id, $data)
    {
        InterventionTraitement::where('id', $id)->limit(1)->update($data);
        return InterventionTraitement::find($id);
    }

    public static function supprimerTraitement($id)
    {
        if (Intervention::where('intervention_traitement_id', '=', $id)->exists()) {
            throw new ArrayException(['message' => 'Impossible de supprimer ce traitement, celui-ci est utilisé dans un exercice.']);
        }
        InterventionTraitement::where('id', $id)->delete();
    }
}
