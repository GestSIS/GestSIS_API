<?php


namespace App\Services;

use App\Business\InterventionBusiness;
use App\Contracts\InterventionRepository;
use App\Exceptions\ArrayValidatorException;
use App\Models\Appel;
use App\Models\GroupeIntervention;
use App\Models\Intervention;
use App\Models\InterventionMateriel;
use App\Models\InterventionSapeur;
use App\Models\InterventionVehicule;
use App\Models\Mission;
use App\Models\Quittance;
use Illuminate\Database\Eloquent\Collection;

class InterventionService
{
    protected $repository;
    protected $business;

    public function __construct(InterventionRepository $repository, InterventionBusiness $business)
    {
        $this->repository = $repository;
        $this->business = $business;
    }

    public function listeIntervention($exercice_comptable_id)
    {
        return $this->repository->listeIntervention($exercice_comptable_id);
    }

    public function getInterventionById($interventionId)
    {
        return $this->repository->findInterventionById($interventionId);
    }

    public function createIntervention($data)
    {
        return $this->business->createIntervention($data);
    }

    public function getInterventionAppels($interventionId)
    {
        return $this->repository->getInterventionAppels($interventionId);
    }

    public function getInterventionMissions($interventionId)
    {
        return $this->repository->getInterventionMissions($interventionId);
    }

    public function getInterventionVehicules($interventionId)
    {
        return $this->repository->getInterventionVehicules($interventionId);
    }

    public function getInterventionMateriels($interventionId)
    {
        return $this->repository->getInterventionMateriels($interventionId);
    }

    public function getInterventionPhases($interventionId)
    {
        return $this->repository->getInterventionPhases($interventionId);
    }

    public function getInterventionQuittances($interventionId)
    {
        return $this->repository->getInterventionQuittances($interventionId);
    }

    public function getInterventionPresences($interventionId)
    {
        return $this->repository->getInterventionPresences($interventionId);
    }

    public function getInterventionGroupes($interventionId)
    {
        return $this->repository->getInterventionGroupes($interventionId);
    }

    //Dispo :
    // Appels
    // Missions
    // Véhicules
    // Materiel
    // Phases
    // Quittances
    // Presences

    /**
     * Updates a intervention.
     *
     * @param int
     * @param array
     * @return Intervention
     * @throws ArrayValidatorException(
     */
    public function editInterventionInformationsById($intervention_id, $data)
    {
        //TODO Update phase debut
        //TODO Check if date debut changed -> update first phase

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

        InterventionSapeur::where('intervention_id', $intervention_id)->delete();
        Intervention::destroy($intervention_id);
    }

    /**
     * Ajout de sapeurs d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addSapeurs($intervention_id, $data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $sapeurs = $data['sapeurs'];

        foreach ($sapeurs as $sapeur) {
            $sapeurId = $sapeur['sapeur_id'];

            $validation = Validator::make($sapeur,
                array(
                    'debut' => 'required|date_format:Y-m-d H:i',
                    'fin' => 'required|date_format:Y-m-d H:i|after:debut',
                    'piquet' => 'required|boolean',
                    'sapeur_id' => 'required|integer|exists:sapeurs,id'
                ));

            //Check période pas dupliquée

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            //TODO Check duplicated period of time
//            if ($this->intervention->sapeurs()->where('intervention_sapeur.sapeur_id', $sapeurId)->first() !== null) {
//                throw new ArrayValidatorException(array('id' => "Duplicated sapeur"));
//            }

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
    public function updateSapeurs($intervention_id, $data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $sapeurs = $data['sapeurs'];

        foreach ($sapeurs as $sapeur) {

            $validation = Validator::make($sapeur,
                array(
                    'debut' => 'required|date_format:Y-m-d H:i',
                    'fin' => 'required|date_format:Y-m-d H:i|after:debut',
                    'piquet' => 'required|boolean',
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $sap = $this->intervention->sapeurs()->where('intervention_sapeur.id', $sapeur['id'])->first();
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
    public function removeSapeurs($interventionId, $data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $ids = $data['sapeurs'];
        $this->repository->removePresencesById($interventionId, $ids);
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

            if (array_key_exists('commentaire', $appel) && $appel['commentaire'] === null) $appel['commentaire'] = '';

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
    public function updateAppels($intervention_id, $data)
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

            if (array_key_exists('commentaire', $appel) && $appel['commentaire'] === null) $appel['commentaire'] = '';

            $app = $this->intervention->appels()->where('appels.id', $appel['id'])->first();
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
    public function removeAppels($interventionId, $data)
    {
        $ids = $data['appels'];
        $this->repository->removeAppelsById($interventionId, $ids);
    }

    /**
     * Ajout de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addMissions($intervention_id, $data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $missions = $data['missions'];

        foreach ($missions as $mission) {
            $validation = Validator::make($mission,
                array(
                    'sapeur_id' => 'integer|exists:sapeurs,id',
                    'debut' => 'required|date_format:Y-m-d H:i',
                    'fin' => 'required|date_format:Y-m-d H:i|after:debut',
                    'titre' => 'string',
                    'resume' => 'string|nullable'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            if (array_key_exists('resume', $mission) && $mission['resume'] === null) $mission['resume'] = '';

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
    public function updateMissions($intervention_id, $data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $missions = $data['missions'];

        foreach ($missions as $mission) {

            $validation = Validator::make($mission,
                array(
                    'id' => 'integer|exists:missions,id',
                    'sapeur_id' => 'integer|exists:sapeurs,id',
                    'debut' => 'date_format:Y-m-d H:i',
                    'fin' => 'date_format:Y-m-d H:i|after:debut',
                    'titre' => 'string',
                    'resume' => 'string|nullable'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            if (array_key_exists('resume', $mission) && $mission['resume'] === null) $mission['resume'] = '';

            $miss = $this->intervention->missions()->where('missions.id', $mission['id'])->first();
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
    public function removeMissions($interventionId, $data)
    {
        $ids = $data['missions'];
        $this->repository->removeMaterielsById($interventionId, $ids);
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addMateriels($intervention_id, $data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $materiels = $data['materiels'];

        foreach ($materiels as $materiel) {
            $validation = Validator::make($materiel,
                array(
                    'materiel_id' => 'required|exists:materiels,id',
                    'quantite' => 'required|integer|min:1'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $mat = new InterventionMateriel();
            $mat->fill($materiel);
            $mat->materiel_id = $materiel['materiel_id'];
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
    public function updateMateriels($intervention_id, $data)
    {
        /* TODO Check:
        - Pas imputé
        */
        $materiels = $data['materiels'];

        foreach ($materiels as $materiel) {

            $validation = Validator::make($materiel,
                array(
                    'id' => 'integer|exists:intervention_materiel,id',
                    'quantite' => 'required|integer|min:1'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $mat = $this->intervention->materiels()->where('intervention_materiel.id', $materiel['id'])->first();
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
    public function removeMateriels($interventionId, $data)
    {
        $ids = $data['materiels'];
        $this->repository->removeMaterielsById($interventionId, $ids);
    }

    /**
     * Ajout de quittances à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addQuittances($intervention_id, $data)
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
    public function removeQuittances($interventionId, $data)
    {
        $ids = $data['quittances'];
        $this->repository->removeQuittancesById($interventionId, $ids);
    }

    /**
     * Ajout de vehicules à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addVehicules($intervention_id, $data)
    {
        /* TODO Check:
        - Pas imputé
        - FIXME Check pas de véhicules dupliqués
        */
        $validation = Validator::make($data,
            array(
                'vehicules' => 'required|array',
                'vehicules.*' => 'required|integer|exists:vehicules,id'
            ));

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        foreach ($data['vehicules'] as $vehicule) {
            $veh = new InterventionVehicule();
            $veh->vehicule_id = $vehicule;
            $this->intervention->vehicules()->save($veh);
        }
        return $this->intervention->vehicules()->get();
    }

    /**
     * Suppression de vehicules à un intervention
     *
     * @param $data
     */
    public function removeVehicules($interventionId, $data)
    {
        $ids = $data['vehicules'];
        $this->repository->removeVehiculesById($interventionId, $ids);
    }

    /**
     * Ajout de groupes à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addGroupes($intervention_id, $data)
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
    public function removeGroupes($interventionId, $data)
    {
        $ids = $data['groupes'];
        $this->repository->removeGroupesById($interventionId, $ids);
    }
}
