<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Models\ControleMedical;
use App\Models\ControleMedicalType;
use App\Models\Medecin;
use Illuminate\Support\Facades\Storage;

class ControleMedicalBusiness
{

    public static function ajouterMedecin($data)
    {
        if (!array_key_exists('adresse', $data) or is_null($data['adresse'])) {
            $data['adresse'] = "";
        }
        $medecin = new Medecin();
        $medecin->fill($data);
        $medecin->save();
        return $medecin;
    }

    public static function modifierMedecin($id, $data)
    {
        Medecin::where('id', $id)->limit(1)->update($data);
        return Medecin::find($id);
    }

    public static function supprimerMedecin($id)
    {
        if (ControleMedical::where('medecin_id', '=', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce médecin, celui-ci est utilisé dans un contrôle médical.');
        }
        Medecin::where('id', $id)->delete();
    }

    public static function ajouterType($data)
    {
        $data['remarque'] = $data['remarque'] ?? '';
        $type = new ControleMedicalType();
        $type->fill($data);
        $type->save();
        return $type;
    }

    public static function modifierType($id, $data)
    {
        $data['remarque'] = $data['remarque'] ?? '';
        ControleMedicalType::where('id', $id)->limit(1)->update($data);
        return ControleMedicalType::find($id);
    }

    public static function supprimerType($id)
    {
        if (ControleMedical::where('controle_medical_type_id', '=', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce type de contrôle médical, celui-ci est utilisé dans un contrôle médical.');
        }
        ControleMedicalType::where('id', $id)->delete();
    }

    public static function createControleMedical($controleMedical)
    {
        //TODO Change this
        $controleMedical['en_cours'] = true;
        $controleMedical['designation'] = $controleMedical['designation'] ?? '';

        $controle = new ControleMedical();
        $controle->fill($controleMedical);
        $controle->sapeur_id = $controleMedical['sapeur_id'];
        $controle->save();

        return $controle;
    }

    public static function updateControleMedical($controleId, $controleMedical)
    {
        //TODO Change this
        $controleMedical['en_cours'] = true;
        $controleMedical['designation'] = $controleMedical['designation'] ?? '';

        ControleMedical::where('id', $controleId)->limit(1)->update($controleMedical);
        return ControleMedical::find($controleId);
    }

    public static function removeControleMedical($controleId)
    {
        //First remove justificatif
        self::removeJustificatif($controleId);
        ControleMedical::destroy($controleId);
    }

    public static function addJustificatif($controleMedicalId, $file, $sisKey)
    {
        //First remove potential already existing document
        self::removeJustificatif($controleMedicalId);

        // Then add the new one
        $path = $file->store('documents/' . $sisKey . '/controles_medicaux');

        $controle = ControleMedical::find($controleMedicalId);
        $controle->filename = $file->getClientOriginalName();
        $controle->path = $path;
        $controle->save();

        return $controle;
    }

    public static function getJustificatif($controleMedicalId)
    {
        //Return the file
        $controle = ControleMedical::find($controleMedicalId);
        return ['path' => $controle->path, 'filename' => $controle->filename];
    }

    public static function removeJustificatif($controleMedicalId)
    {
        $controle = ControleMedical::find($controleMedicalId);
        if ($controle && $controle->path) {
            $path = $controle->path;
            $controle->filename = null;
            $controle->path = null;
            $controle->save();
            Storage::delete($path);
        }
    }
}
