<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\Emplacement;

class MaterielBusiness
{
    public function ajouterEmplacement($data)
    {
        $emplacement = new Emplacement();
        $emplacement->fill($data);
        $emplacement->save();
        return $emplacement;
    }

    public static function modifierEmplacement($id, $data)
    {
        Emplacement::where('id', $id)->limit(1)->update($data);
        return Emplacement::find($id);
    }

    public static function supprimerEmplacement($id)
    {
        // TODO:
    }
}
