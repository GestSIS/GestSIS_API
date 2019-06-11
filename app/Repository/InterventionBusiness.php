<?php


namespace App\Repository;


use App\Exceptions\ArrayValidatorException;
use App\Models\Intervention;
use App\Models\GroupeIntervention;
use App\Models\InterventionMateriel;
use App\Models\InterventionSapeur;
use App\Models\InterventionVehicule;
use App\Models\Appel;
use App\Models\Mission;
use App\Models\Quittance;
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
     * Return intervention data
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
     * @throws ArrayValidatorException
     */
    public static function createIntervention($data)
    {
        //TODO Vérifier exercice comptable
        $validation = Validator::make($data,
            array(
                'date_debut' => 'date',
                'heure_debut' => 'date_format:H:i',
                'date_fin' => 'date|after_or_equal:date_debut',
                'heure_fin' => 'date_format:H:i',
                'lieu' => 'string|nullable',
                'objet' => 'string',
                'rapport_police' => 'boolean',
                'degre' => 'integer|min:1|max:3',
                'sauve_personne' => 'integer|min:0|max:50',
                'sauve_animaux' => 'integer|min:0|max:50',
                'description' => 'string|nullable',
                'proprietaire' => 'string|nullable',
                'responsable' => 'string|nullable',
                'stat_nb' => 'integer|min:0',
                'imputer' => 'boolean',
                'localite_id' => 'integer|exists:localites,id',
                'exercice_comptable_id' => 'integer|exists:exercice_comptables,id',
                'intervention_traitement_id' => 'integer|exists:intervention_traitements,id',
                'stat_federal_id' => 'integer|exists:stat_federals,id',
                'sapeur_id' => 'integer|exists:sapeurs,id',
                'type_intervention_id' => 'integer|exists:type_interventions,id',
            ));

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        if ($data['lieu'] === null) $data['lieu'] = '';
        if ($data['description'] === null) $data['description'] = '';
        if ($data['proprietaire'] === null) $data['proprietaire'] = '';
        if ($data['responsable'] === null) $data['responsable'] = '';


        $intervention = new Intervention();
        $intervention->fill($data);

        $intervention->imputer = false;
        $intervention->exercice_comptable_id = $data['exercice_comptable_id'];

        $intervention->save();

        return new InterventionBusiness($intervention);
    }

    /**
     * Updates a intervention.
     *
     * @param int
     * @param array
     * @return Intervention
     * @throws ArrayValidatorException(
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
            throw new ArrayValidatorException($validation->errors());
        }

        if ($data['lieu'] === null) $data['lieu'] = '';
        if ($data['designation'] === null) $data['designation'] = '';

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
        /* TODO Check:
        - Pas imputé
        */

        /* TODO: Suppression:
        - Sapeur
        - Groupes
        - Vehicules
        - Matériel
        - Quittances
        - Missions
        - Appels
        */

        InterventionSapeur::where('exercice_id', $intervention_id)->delete();
        Intervention::destroy($intervention_id);
    }


    /**
     * Ajout de sapeurs d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addSapeurs($data)
    {
        /* TODO Check:
        - Pas imputé
        */
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

            //Check période pas dupliquée

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            if ($this->intervention->sapeurs()->where('exercice_sapeur.sapeur_id', $sapeurId)->first() !== null) {
                throw new ArrayValidatorException(array('id' => "Duplicated sapeur"));
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
     * @throws ArrayValidatorException(
     */
    public function updateSapeurs($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $sapeurs = $data['sapeurs'];

        foreach ($sapeurs as $sapeur) {

            $validation = Validator::make($sapeur,
                array(
                    'debut' => 'required|date_format:Y-m-d H:i:s',
                    'fin' => 'required|date_format:Y-m-d H:i:s|after:debut',
                    'piquet' => 'required|boolean',
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $sap = $this->intervention->sapeurs()->where('exercice_sapeur.id', $sapeur['id'])->first();
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
        /* TODO Check:
        - Pas imputé
        */
        $ids = $data['sapeurs'];

        $this->intervention->sapeurs()->whereIn('exercice_sapeur.id', $ids)->delete();
    }


    /**
     * Ajout d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addAppels($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $appels = $data['$appels'];

        foreach ($appels as $appel) {
            $validation = Validator::make($appel,
                array(
                    'date' => 'required|date_format:Y-m-d H:i',
                    'numero' => 'string',
                    'nom' => 'string',
                    'commentaire' => 'string|nullable'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            if ($data['commentaire'] === null) $data['commentaire'] = '';

            $app = new Appel();
            $app->fill($appel);
            $this->intervention->appels()->save($app);
        }
        return $this->intervention->appels()->get();
    }

    /**
     * Modification d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updateAppels($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $appels = $data['appels'];

        foreach ($appels as $appel) {

            $validation = Validator::make($appel,
                array(
                    'date' => 'required|date_format:Y-m-d H:i',
                    'numero' => 'string',
                    'nom' => 'string',
                    'commentaire' => 'string|nullable'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            if ($data['commentaire'] === null) $data['commentaire'] = '';

            $app = $this->intervention->appels()->where('appel.id', $appel['id'])->first();
            $app->update($appel);
            $app->save();
        }
        return $this->intervention->appels()->get();
    }

    /**
     * Suppression d'appels d'un intervention
     *
     * @param $data
     */
    public function removeAppels($data)
    {
        $ids = $data['appels'];

        $this->intervention->sapeurs()->whereIn('appel.id', $ids)->delete();
    }

    /**
     * Ajout de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addMissions($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $missions = $data['missions'];

        foreach ($missions as $mission) {
            $validation = Validator::make($mission,
                array(
                    'debut' => 'required|date_format:Y-m-d H:i',
                    'fin' => 'required|date_format:Y-m-d H:i|after:debut',
                    'titre' => 'string',
                    'resume' => 'string|nullable'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            if ($data['resume'] === null) $data['resume'] = '';

            $miss = new Mission();
            $miss->fill($mission);
            $this->intervention->missions()->save($miss);
        }
        return $this->intervention->missions()->get();
    }

    /**
     * Modification de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updateMissions($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $missions = $data['missions'];

        foreach ($missions as $mission) {

            $validation = Validator::make($mission,
                array(
                    'id' => 'exists|intervention_mission,id',
                    'debut' => 'date_format:Y-m-d H:i',
                    'fin' => 'date_format:Y-m-d H:i|after:debut',
                    'titre' => 'string',
                    'resume' => 'string|nullable'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            if ($data['resume'] === null) $data['resume'] = '';

            $miss = $this->intervention->missions()->where('mission.id', $mission['id'])->first();
            $miss->update($mission);
            $miss->save();
        }
        return $this->intervention->missions()->get();
    }

    /**
     * Suppression de missions à un intervention
     *
     * @param $data
     */
    public function removeMissions($data)
    {
        $ids = $data['missions'];

        $this->intervention->missions()->whereIn('mission.id', $ids)->delete();
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addMateriels($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $materiels = $data['materiels'];

        foreach ($materiels as $materiel) {
            $validation = Validator::make($materiel,
                array(
                    'materiel_id' => 'required|exists:materiels,id',
                    'quantite' => 'required|integer|min:1',
                    'utilisation' => 'required|integer|min:1',
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $mat = new InterventionMateriel();
            $mat->fill($materiel);
            $mat->forfait = 0;
            $mat->unite = 0;
            $this->intervention->materiels()->save($mat);
        }
        return $this->intervention->materiels()->get();
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updateMateriels($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $materiels = $data['materiels'];

        foreach ($materiels as $materiel) {

            $validation = Validator::make($materiel,
                array(
                    'id' => 'exists|intervention_materiel,id',
                    'quantite' => 'required|integer|min:1',
                    'utilisation' => 'required|integer|min:1',
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $mat = $this->intervention->materiels()->where('materiel.id', $materiel['id'])->first();
            $mat->update($materiel);
            $mat->save();
        }
        return $this->intervention->materiels()->get();
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public function removeMateriels($data)
    {
        $ids = $data['materiels'];

        $this->intervention->materiels()->whereIn('materiel.id', $ids)->delete();
    }

    /**
     * Ajout de quittances à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addQuittances($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $quittances = $data['quittances'];

        foreach ($quittances as $quittance) {
            $validation = Validator::make($quittance,
                array(
                    'sapeur_id' => 'required|exists:sapeurs,id'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $quit = new Quittance();
            $quit->sapeur_id = $quittance['sapeur_id'];
            $this->intervention->quittances()->save($quit);
        }
        return $this->intervention->quittances()->get();
    }


    /**
     * Suppression de quittances à un intervention
     *
     * @param $data
     */
    public function removeQuittances($data)
    {
        $ids = $data['quittances'];

        $this->intervention->quittances()->whereIn('quittance.id', $ids)->delete();
    }

    /**
     * Ajout de vehicules à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addVehicules($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $vehicules = $data['vehicules'];

        foreach ($vehicules as $vehicule) {
            $validation = Validator::make($vehicule,
                array(
                    'vehicule_id' => 'required|exists:vehicules,id',
                    'utilisation' => 'required|integer|min:1',
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $veh = new InterventionVehicule();
            $veh->fill($vehicule);
            $veh->forfait = 0;
            $veh->unite = 0;
            $this->intervention->vehicules()->save($veh);
        }
        return $this->intervention->vehicules()->get();
    }

    /**
     * Modification de vehicules à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updateVehicules($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $vehicules = $data['vehicules'];

        foreach ($vehicules as $vehicule) {

            $validation = Validator::make($vehicule,
                array(
                    'id' => 'exists|intervention_vehicule,id',
                    'utilisation' => 'required|integer|min:1',
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $veh = $this->intervention->vehicules()->where('vehicule.id', $vehicule['id'])->first();
            $veh->update($vehicule);
            $veh->save();
        }
        return $this->intervention->vehicules()->get();
    }

    /**
     * Suppression de vehicules à un intervention
     *
     * @param $data
     */
    public function removeVehicules($data)
    {
        $ids = $data['vehciules'];

        $this->intervention->vehicules()->whereIn('vehicule.id', $ids)->delete();
    }

    /**
     * Ajout de groupes à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addGroupes($data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $groupes = $data['groupes'];

        foreach ($groupes as $groupe) {
            $validation = Validator::make($groupe,
                array(
                    'groupe_id' => 'required|exists:groupes,id'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $grp = new GroupeIntervention();
            $grp->groupe_id = $groupe['gropupe_id'];
            $this->intervention->groupes()->save($grp);
        }
        return $this->intervention->groupes()->get();
    }

    /**
     * Suppression de groupes à un intervention
     *
     * @param $data
     */
    public function removeGroupes($data)
    {
        $ids = $data['groupes'];

        $this->intervention->groupes()->whereIn('groupe.id', $ids)->delete();
    }
}
