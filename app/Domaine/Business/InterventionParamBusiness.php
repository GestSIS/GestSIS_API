<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\InterventionTraitement;
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
        //TODO: Not implemented now
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
        //TODO: Not implemented now
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
        //TODO: Not implemented now
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
        //TODO: Not implemented now
    }

    public function ajouterVehicule($data)
    {
        $vehicule = new Vehicule();
        $vehicule->fill($data);
        $vehicule->save();
        return $vehicule;
    }

    public function modifierVehicule($id, $data)
    {
        Vehicule::where('id', $id)->limit(1)->update($data);
        return Vehicule::find($id);
    }

    public static function supprimerVehicule($id)
    {
        //TODO: Not implemented now
    }

    public function ajouterMateriel($data)
    {
        $materiel = new Materiel();
        $materiel->fill($data);
        $materiel->save();
        return $materiel;
    }

    public function modifierMateriel($id, $data)
    {
        Materiel::where('id', $id)->limit(1)->update($data);
        return Materiel::find($id);
    }

    public static function supprimerMateriel($id)
    {
        //TODO: Not implemented now
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
        //TODO: Not implemented now
    }
}
