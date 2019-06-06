<?php


namespace App\Repository;


use App\Models\ExerciceSapeur;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class InterventionBusiness
{


    /**
     * Ajout de sapeurs d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function addSapeurs($data)
    {
        $sapeurs = $data['sapeurs'];

        foreach ($sapeurs as $sapeur) {
            $sapeurId = $sapeur['sapeur_id'];
            $validation = Validator::make($sapeur,
                array(
                    'convoque' => 'required|boolean',
                    'present' => 'required|boolean',
                    'amende' => 'required|boolean',
                    'remplace' => 'required|boolean',
                    'excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
                    'sapeur_id' => 'required|integer|exists:sapeurs,id'
                ));

            if ($validation->fails()) {
                throw new Exception($validation->messages());
            }

            if ($this->intervention->sapeurs()->where('exercice_sapeur.sapeur_id', $sapeurId)->first() !== null) {
                throw new Exception("Duplicated sapeur");
            }

            $sap = new ExerciceSapeur();
            $sap->fill($sapeur);
            $sap->sapeur_id = $sapeur['sapeur_id'];
            $this->intervention->sapeurs()->save($sap);
        }
        return $this->intervention->sapeurs()->get();
    }

    /**
     * Modification de sapeurs d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function updateSapeurs($data)
    {
        $sapeurs = $data['sapeurs'];

        foreach ($sapeurs as $sapeur) {

            $sap = $this->intervention->sapeurs()->where('exercice_sapeur.id', $sapeur['id'])->first();
            $validation = Validator::make($sapeur,
                array(
                    'convoque' => 'required|boolean',
                    'present' => 'required|boolean',
                    'amende' => 'required|boolean',
                    'remplace' => 'required|boolean',
                    'excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
                ));

            if ($validation->fails()) {
                throw new Exception($validation->messages());
            }

            $sap->update($sapeur);
            $sap->save();
        }
        return $this->intervention->sapeurs()->get();
    }

    /**
     * Suppression de sapeurs d'un intervention
     *
     * @param $data
     */
    public function removeSapeurs($data)
    {
        $ids = $data['sapeurs'];

        $this->intervention->sapeurs()->whereIn('exercice_sapeur.id', $ids)->delete();
    }


    /**
     * Ajout d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function addAppels($data)
    {
        //TODO
    }

    /**
     * Modification d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function updateAppels($data)
    {
        //TODO
    }

    /**
     * Suppression d'appels d'un intervention
     *
     * @param $data
     */
    public function removeAppels($data)
    {
        //TODO
    }

    /**
     * Ajout de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function addMissions($data)
    {
        //TODO
    }

    /**
     * Modification de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function updateMissions($data)
    {
        //TODO
    }

    /**
     * Suppression de missions à un intervention
     *
     * @param $data
     */
    public function removeMissions($data)
    {
        //TODO
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function addMateriels($data)
    {
        //TODO
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function updateMateriels($data)
    {
        //TODO
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public function removeMateriels($data)
    {
        //TODO
    }

    /**
     * Ajout de quittances à un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function addQuittances($data)
    {
        //TODO
    }

    /**
     * Modification de quittances à un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function updateQuittances($data)
    {
        //TODO
    }

    /**
     * Suppression de quittances à un intervention
     *
     * @param $data
     */
    public function removeQuittances($data)
    {
        //TODO
    }

    /**
     * Ajout de vehicules à un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function addVehicules($data)
    {
        //TODO
    }

    /**
     * Modification de vehicules à un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function updateVehicules($data)
    {
        //TODO
    }

    /**
     * Suppression de vehicules à un intervention
     *
     * @param $data
     */
    public function removeVehicules($data)
    {
        //TODO
    }

    /**
     * Ajout de groupes à un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function addGroupes($data)
    {
        //TODO
    }

    /**
     * Modification de groupes à un intervention
     *
     * @param $data
     * @return Collection
     * @throws Exception
     */
    public function updateGroupes($data)
    {
        //TODO
    }

    /**
     * Suppression de groupes à un intervention
     *
     * @param $data
     */
    public function removeGroupes($data)
    {
        //TODO
    }
}
