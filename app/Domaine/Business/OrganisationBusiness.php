<?php


namespace App\Domaine\Business;

use App\Infrastructure\Models\Groupe;

class OrganisationBusiness
{
    
    public function createGroup($data)
    {
        // TODO: Check pere_id ?
        $groupe = new Groupe();
        $groupe->fill($data);
        $groupe->save();

        return $groupe;
    }

    public function updateGroupe($data)
    {

    }

    public function deleteGroupe($data)
    {
        
    }
}