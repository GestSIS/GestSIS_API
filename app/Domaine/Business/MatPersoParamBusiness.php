<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\MaterielAlerteType;
use App\Infrastructure\Models\MaterielCategorie;
use App\Infrastructure\Models\MaterielEvent;
use App\Infrastructure\Models\MaterielEventType;
use App\Infrastructure\Models\MaterielType;

class MatPersoParamBusiness
{
    // Categorie
    public static function ajouterCategorie($data)
    {
        // TODO: Controller récursivity du parent
        $categorie = new MaterielCategorie();
        $categorie->fill($data);
        $categorie->save();

        return $categorie;
    }

    public static function modifierCategorie($id, $data)
    {
        // TODO: Controller récursivity du parent
        MaterielCategorie::where('id', $id)->limit(1)->update($data);
        return MaterielCategorie::find($id);
    }

    public static function supprimerCategorie($id)
    {
        // TODO: Controller qu'il n'y a aucun enfant
        MaterielCategorie::where('id', $id)->delete();
    }

    // Type
    public static function ajouterType($data)
    {
        $type = new MaterielType();
        $type->fill($data);
        $type->save();
        return $type;
    }

    public static function modifierType($id, $data)
    {
        MaterielType::where('id', $id)->limit(1)->update($data);
        return MaterielType::find($id);
    }

    public static function supprimerType($id)
    {
        MaterielType::where('id', $id)->delete();
    }

    // Alerte type
    public static function ajouterAlerteType($data)
    {
        $alerte = new MaterielAlerteType();
        $alerte->fill($data);
        $alerte->save();

        $alerte->eventTypes()->sync($data['eventTypeIds']);
        return $alerte;
    }

    public static function modifierAlerteType($id, $data)
    {
        $alerte = MaterielAlerteType::with('eventTypes')->find($id);
        $alerte->update($data);
        $alerte->eventTypes()->sync($data['eventTypeIds']);
        return MaterielAlerteType::with('eventTypes')->find($id);
    }

    public static function supprimerAlerteType($id)
    {
        MaterielAlerteType::where('id', $id)->delete();
    }

    // Event type
    public static function ajouterEventType($data)
    {
        $event = new MaterielEventType();
        $event->fill($data);
        $event->save();

        $event->materielTypes()->sync($data['materielTypeIds']);
        return $event;
    }

    public static function modifierEventType($id, $data)
    {
        $event = MaterielEventType::with('materielTypes')->find($id);
        $event->update($data);
        $event->materielTypes()->sync($data['materielTypeIds']);
        return MaterielEventType::with('materielTypes')->find($id);
    }

    public static function supprimerEventType($id)
    {
        if (MaterielEvent::where('materiel_event_id', '=', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer cet événement type car celle-ci est utilisé.');
        }
        MaterielEventType::where('id', $id)->delete();
    }
}
