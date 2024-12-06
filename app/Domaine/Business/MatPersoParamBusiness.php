<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\MaterielAlerteType;
use App\Infrastructure\Models\MaterielCategorie;
use App\Infrastructure\Models\MaterielEvent;
use App\Infrastructure\Models\MaterielEventType;
use App\Infrastructure\Models\MaterielType;

// TODO: Supprimer
class MatPersoParamBusiness
{
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
        $alerte = MaterielAlerteType::find($id);
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
        $event = MaterielEventType::find($id);
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
