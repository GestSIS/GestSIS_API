<?php

namespace App\Domaine\Business;

use App\Domaine\SPI\InterventionRepository;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\InterventionSapeur;
use App\Infrastructure\Models\Mission;
use App\Infrastructure\Models\Quittance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class InterventionBusiness
{

    public const INTERVENTION_STATUT_EMPTY = 0;
    public const INTERVENTION_STATUT_SAISI = 1;
    public const INTERVENTION_STATUT_VALIDE = 2;
    public const INTERVENTION_STATUT_IMPUTE = 3;

    protected $repository;

    public function __construct(InterventionRepository $repository)
    {
        $this->repository = $repository;
    }

    private function checkIsNotImpute($interventionId)
    {
        $statut = $this->repository->getInterventionStatutById($interventionId);
        if ($statut >= self::INTERVENTION_STATUT_IMPUTE) {
            throw new ArrayException([], 'Intervention already impute');
        }
    }

    /**
     * Create a intervention
     *
     * @param $data
     * @return InterventionBusiness
     * @throws ArrayException
     */
    public function createIntervention($data)
    {
        //TODO Vérifier intervention comptable
        $phaseTypeIntervention = 1;
        $data['statut'] = self::INTERVENTION_STATUT_EMPTY;

        $intervention = $this->repository->createNewIntervention($data);
        $this->repository->addPhase($intervention->id, array(
            "debut" => null,
            "phase_type_id" => $phaseTypeIntervention,
        ));
        return $intervention;
    }

    /**
     * Import an intervention
     *
     * @param $data
     * @return InterventionBusiness
     * @throws ArrayException
     */
    public function importIntervention($intervention, $sapeurs, $groupes, $missions, $appels, $vehicules, $materiel, $quittances)
    {
        $phaseTypeIntervention = 1;
        $intervention['statut'] = self::INTERVENTION_STATUT_SAISI;
        $intervention['intervention_traitement_id'] = 1; // TODO: config à ajouter dans params

        // Identification de l'exercice comptable à utiliser
        $exerciceComptable = ExerciceComptable::where([
            ['debut', '<=', $intervention['date_debut']],
            ['fin', '>=', $intervention['date_debut']],
        ])->first();

        // Création de l'exercice comptable automatique si année en cours et aucun exercice comptable existant
        $anneeEnCours = Carbon::now()->year;
        if ($exerciceComptable == NULL && $anneeEnCours == Carbon::parse($intervention['date_debut'])->year) {
            // Création de l'exercice comptable
            $exerciceComptable = new ExerciceComptable();
            $exerciceComptable->annee = $anneeEnCours;
            $exerciceComptable->designation = "Année comptable " . $anneeEnCours;
            $exerciceComptable->debut = Carbon::createFromDate($anneeEnCours, 1, 1);
            $exerciceComptable->fin = Carbon::createFromDate($anneeEnCours, 12, 31);
            $exerciceComptable->boucle = false;
            $exerciceComptable->save();
        }

        // Check pas déjà cloturé
        if ($exerciceComptable == NULL || $exerciceComptable->boucle) {
            // Impossible d'ajouter l'intervention
            throw new ArrayException(["message" => "Exercice comptable inexistant ou déjà bouclé"]);
        }

        $intervention['exercice_comptable_id'] = $exerciceComptable->id;
        if (!array_key_exists('lieu', $intervention) || is_null($intervention['lieu'])) $intervention['lieu'] = '';
        if (!array_key_exists('description', $intervention) || is_null($intervention['description'])) $intervention['description'] = '';
        if (!array_key_exists('proprietaire', $intervention) || is_null($intervention['proprietaire'])) $intervention['proprietaire'] = '';
        if (!array_key_exists('responsable', $intervention) || is_null($intervention['responsable'])) $intervention['responsable'] = '';

        $newIntervention = new Intervention();
        $newIntervention->fill($intervention);
        $newIntervention->date_imputation = null;
        $newIntervention->exercice_comptable_id = $intervention['exercice_comptable_id'];
        $newIntervention->save();

        // Pour le moment pas de gestion des phases dans GestSIS Mobile
        $this->repository->addPhase($newIntervention->id, array(
            "debut" => null,
            "phase_type_id" => $phaseTypeIntervention,
        ));

        // Ajout des quittances
        $quittances = array_map(function ($e) use ($newIntervention) {
            return ['intervention_id' => $newIntervention->id, 'sapeur_id' => $e];
        }, array_unique($quittances));
        Quittance::insert($quittances);

        // Ajout des sapeurs
        $sapeurs = array_map(function ($e) use ($newIntervention) {
            $e['intervention_id'] = $newIntervention->id;
            return $e;
        }, $sapeurs);
        InterventionSapeur::insert($sapeurs);

        // Ajout des groupes
        $groupes = array_map(function ($e) use ($newIntervention) {
            $e['intervention_id'] = $newIntervention->id;
            return $e;
        }, $groupes);
        $newIntervention->groupes()->insert($groupes);

        // Ajout des missions
        $missions = array_map(function ($e) use ($newIntervention) {
            if (!isset($e['resume']) || is_null($e['resume'])) $e['resume'] = '';
            if (!isset($e['sapeur_id']) && $e['resume'] == 'CR') $e['sapeur_id'] = null;
            $e['intervention_id'] = $newIntervention->id;
            return $e;
        }, $missions);
        $newIntervention->missions()->insert($missions);

        // Ajout des appels
        $appels = array_map(function ($e) use ($newIntervention) {
            if (!isset($e['commentaire']) || is_null($e['commentaire'])) $e['commentaire'] = '';
            $e['intervention_id'] = $newIntervention->id;
            return $e;
        }, $appels);
        $newIntervention->appels()->insert($appels);

        // Ajout des vehicules
        $newIntervention->vehiculesInter()->attach($vehicules);

        // Ajout du matériel
        $materiel = array_map(function ($e) use ($newIntervention) {
            $e['intervention_id'] = $newIntervention->id;
            return $e;
        }, $materiel);
        $newIntervention->materiels()->insert($materiel);

        return $newIntervention->toArray();
    }

    /**
     * @param $interventionId
     * @return mixed
     * @throws ArrayException
     */
    public function validerInterventionById($interventionId)
    {
        $statut = $this->repository->getInterventionStatutById($interventionId);
        if ($statut === self::INTERVENTION_STATUT_SAISI) {
            return $this->repository->editInterventionInformationsById($interventionId, [
                "statut" => self::INTERVENTION_STATUT_VALIDE
            ])->statut;
        }
        throw new ArrayException(["message" => "Impossible de valider l'exercice."]);
    }

    /**
     * Updates a intervention.
     *
     * @param int
     * @param array
     * @return Intervention
     * @throws ArrayException(
     */
    public function editInterventionInformationsById($interventionId, $data)
    {
        $intervention = $this->repository->editInterventionInformationsById($interventionId, $data);
        //TODO Update phase debut
        //TODO Check if date debut changed -> update first phase

        return $intervention;
    }

    /**
     * Delete a intervention.
     *
     * @param int
     */
    public function deleteInterventionById($interventionId)
    {
        $this->checkIsNotImpute($interventionId);
        $this->repository->supprimerInterventionById($interventionId);
        return true;
    }

    /**
     * Ajout de sapeurs d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function addPresences($interventionId, $sapeurs)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($sapeurs as $sapeur) {
            // TODO: Check duplicated period of time

            $this->repository->addPresence($interventionId, $sapeur);
        }

        $statut = $this->repository->getInterventionStatutById($interventionId);
        if ($statut < self::INTERVENTION_STATUT_SAISI) {
            $statut = $this->repository->editInterventionInformationsById($interventionId, ["statut" => self::INTERVENTION_STATUT_SAISI])->statut;
        }
        return $statut;
    }

    /**
     * Modification de sapeurs d'une intervention
     *
     * @param $data
     * @return Collection
     */
    public function updatePresences($interventionId, $sapeurs)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($sapeurs as $sapeur) {
            // TODO: Check period non dupliqué

            $this->repository->editPresenceInfoById($interventionId, $sapeur['id'], $sapeur);
        }
    }

    /**
     * Suppression de sapeurs d'un intervention
     *
     * @param $data
     */
    public function removePresences($interventionId, $ids)
    {
        $this->checkIsNotImpute($interventionId);

        $this->repository->removePresencesById($interventionId, $ids);
        $statut = $this->repository->getInterventionStatutById($interventionId);

        if (count($this->repository->getInterventionPresences($interventionId)) === 0) {
            $statut = $this->repository->editInterventionInformationsById($interventionId, ["statut" => self::INTERVENTION_STATUT_EMPTY]);
        }
        return $statut;
    }

    /**
     * Ajout d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function addAppels($interventionId, $appels)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($appels as $appel) {
            $this->repository->addAppel($interventionId, $appel);
        }
    }

    /**
     * Modification d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function updateAppels($interventionId, $appels)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($appels as $appel) {
            $this->repository->editAppelInfoById($interventionId, $appel['id'], $appel);
        }
    }

    /**
     * Suppression d'appels d'une intervention
     *
     * @param $data
     */
    public function removeAppels($interventionId, $ids)
    {
        $this->checkIsNotImpute($interventionId);

        $this->repository->removeAppelsById($interventionId, $ids);
    }

    /**
     * Ajout de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function addMissions($interventionId, $missions)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($missions as $mission) {
            $this->repository->addMission($interventionId, $mission);
        }
    }

    /**
     * Modification de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function updateMissions($interventionId, $missions)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($missions as $mission) {
            $this->repository->editMissionInfoById($interventionId, $mission['id'], $mission);
        }
    }

    /**
     * Suppression de missions à un intervention
     *
     * @param $data
     */
    public function removeMissions($interventionId, $ids)
    {
        $this->checkIsNotImpute($interventionId);

        $this->repository->removeMissionsById($interventionId, $ids);
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function addPhases($interventionId, $phases)
    {
        $this->checkIsNotImpute($interventionId);

        $intervention = $this->repository->findInterventionById($interventionId);
        $existingPhases = $this->repository->getInterventionPhases($interventionId);

        $debut = Carbon::parse($intervention->date_debut . " " . $intervention->heure_debut);
        foreach ($phases as $phase) {
            if ($debut >= Carbon::parse($phase['debut'])) {
                throw new ArrayException(["debut" => "Debut trop tôt"]);
            } else {
                foreach ($existingPhases as $existingPhase) {
                    if ($existingPhase->debut !== null && $debut === Carbon::parse($existingPhase->debut)) {
                        throw new ArrayException(["debut" => "Duplicated phase at same time"]);
                    }
                }
                $this->repository->addPhase($interventionId, $phase);
            }
        }
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function updatePhases($interventionId, $phases)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($phases as $phase) {
            $this->repository->editPhaseInfosById($interventionId, $phase['id'], $phase);
        }
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public function removePhases($interventionId, $ids)
    {
        $this->checkIsNotImpute($interventionId);

        $this->repository->removePhasesById($interventionId, $ids);
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function addMateriels($interventionId, $materiels)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($materiels as $materiel) {
            $this->repository->addMateriel($interventionId, $materiel);
        }
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function updateMateriels($interventionId, $materiels)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($materiels as $materiel) {
            $this->repository->editMaterielQuantiteById($interventionId, $materiel['id'], $materiel['quantite']);
        }
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public function removeMateriels($interventionId, $ids)
    {
        $this->checkIsNotImpute($interventionId);

        $this->repository->removeMaterielsById($interventionId, $ids);
    }

    /**
     * Ajout de quittances à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function addQuittances($interventionId, $quittances)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($quittances as $quittance) {
            $this->repository->addQuittance($interventionId, $quittance);
        }
    }

    /**
     * Suppression de quittances à un intervention
     *
     * @param $data
     */
    public function removeQuittances($interventionId, $ids)
    {
        $this->checkIsNotImpute($interventionId);

        $this->repository->removeQuittancesById($interventionId, $ids);
    }

    /**
     * Ajout de vehicules à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function addVehicules($interventionId, $vehicules)
    {
        $this->checkIsNotImpute($interventionId);

        //Check duplicated vehicules
        $vehiculesRef = $this->repository->getInterventionVehicules($interventionId);
        $vehiculesId = array_map(function ($vehicule) {
            return $vehicule->vehicule_id;
        }, $vehiculesRef);
        $vehicules = array_filter($vehicules, function ($vehicule) use ($vehiculesId) {
            return !in_array($vehicule, $vehiculesId);
        });

        foreach ($vehicules as $vehicule) {
            $this->repository->addVehicule($interventionId, $vehicule);
        }
    }

    /**
     * Suppression de vehicules à un intervention
     *
     * @param $data
     */
    public function removeVehicules($interventionId, $ids)
    {
        $this->checkIsNotImpute($interventionId);

        $this->repository->removeVehiculesById($interventionId, $ids);
    }

    /**
     * Ajout de groupes à une intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function addGroupes($interventionId, $groupes)
    {
        $this->checkIsNotImpute($interventionId);

        foreach ($groupes as $groupe) {
            $this->repository->addGroupe($interventionId, $groupe['no'], $groupe['designation']);
        }
    }

    /**
     * Suppression de groupes à un intervention
     *
     * @param $data
     */
    public function removeGroupes($interventionId, $ids)
    {
        $this->checkIsNotImpute($interventionId);

        $this->repository->removeGroupesById($interventionId, $ids);
    }
}
