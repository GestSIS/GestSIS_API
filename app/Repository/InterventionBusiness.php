<?php


namespace App\Repository;


use App\Models\Intervention;
use App\Models\InterventionSapeur;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class InterventionBusiness
{

    protected $intervention;

    public function __construct(Intervention $intervention)
    {
        $this->intervention = $intervention;
    }

    /**
     * Get's a intervention by it's ID
     *
     * @param int
     * @return InterventionBusiness
     */
    public static function get($intervention_id)
    {
        return new InterventionBusiness(Intervention::findOrFail($intervention_id));
    }

    /**
     * Return sapeur data
     *
     * @return Intervention
     */
    public function getData()
    {
        return $this->intervention;
    }

    /**
     * Create a intervention
     *
     * @param $data
     * @return InterventionBusiness
     * @throws Exception
     */
    public static function createIntervention($data)
    {
        $validation = Validator::make($data,
            array(
                'date' => 'date',
                'heure' => 'date_format:H:i:s',
                'lieu' => 'string|nullable',
                'communication' => 'string',
                'designation' => 'string|nullable',
                'duree' => 'integer',
                'status' => 'integer',
                'exercice_categorie_id' => 'integer|exists:exercice_categories,id',
                'localite_id' => 'integer|exists:localites,id',
                'exercice_comptable_id' => 'integer|exists:exercice_comptables,id'
            ));

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        if ($data['lieu'] === null) {
            $data['lieu'] = '';
        }
        if ($data['designation'] === null) {
            $data['designation'] = '';
        }

        $intervention = new Intervention();
        $intervention->fill($data);
        $intervention->exercice_categorie_id = $data['exercice_categorie_id'];
        $intervention->exercice_comptable_id = $data['exercice_comptable_id'];
        $intervention->save();

        return new InterventionBusiness($intervention);
    }

    /**
     * Updates a post.
     *
     * @param int
     * @param array
     * @return Intervention
     * @throws Exception
     */
    public function update($data)
    {
        $validation = Validator::make($data,
            array(
                'date' => 'date',
                'heure' => 'date_format:H:i:s',
                'lieu' => 'string|nullable',
                'communication' => 'string',
                'designation' => 'string|nullable',
                'duree' => 'integer',
                'status' => 'integer',
                'exercice_categorie_id' => 'integer|exists:exercice_categories,id',
                'localite_id' => 'integer|exists:localites,id'
            ));

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        if ($data['lieu'] === null) {
            $data['lieu'] = '';
        }
        if ($data['designation'] === null) {
            $data['designation'] = '';
        }

        $this->intervention->update($data);

        return $this->intervention;
    }

    /**
     * Delete a intervention.
     *
     * @param int
     */
    public static function delete($intervention_id)
    {
        InterventionSapeur::where('exercice_id', $intervention_id)->delete();
        Intervention::destroy($intervention_id);
    }


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
                    'debut' => 'required|date_format:Y-m-d H:i:s',
                    'fin' => 'required|date_format:Y-m-d H:i:s|after:debut',
                    'piquet' => 'required|boolean',
                    'sapeur_id' => 'required|integer|exists:sapeurs,id'
                ));

            if ($validation->fails()) {
                throw new Exception($validation->messages());
            }

            if ($this->intervention->sapeurs()->where('exercice_sapeur.sapeur_id', $sapeurId)->first() !== null) {
                throw new Exception("Duplicated sapeur");
            }

            $sap = new InterventionSapeur();
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
                    'debut' => 'required|date_format:Y-m-d H:i:s',
                    'fin' => 'required|date_format:Y-m-d H:i:s|after:debut',
                    'piquet' => 'required|boolean',
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
