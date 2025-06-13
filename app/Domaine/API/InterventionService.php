<?php


namespace App\Domaine\API;

use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Domaine\Business\SisParamBusiness;
use App\Domaine\SPI\InterventionRepository;
use App\Infrastructure\Models\Article;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Groupe;
use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\Materiel;
use App\Infrastructure\Models\Quittance;
use App\Infrastructure\Models\Sapeur;
use App\Infrastructure\Models\Vehicule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InterventionService
{
    protected $repository;
    protected $business;

    public function __construct(InterventionRepository $repository, InterventionBusiness $business)
    {
        $this->repository = $repository;
        $this->business = $business;
    }

    public function listeIntervention($exerciceComptableId)
    {
        return $this->repository->listeIntervention($exerciceComptableId);
    }

    public function getInterventionById($interventionId)
    {
        return $this->repository->findInterventionById($interventionId);
    }

    public function createIntervention($data)
    {
        return $this->business->createIntervention($data);
    }

    public function importIntervention($intervention, $sapeurs, $groupes, $missions, $appels, $vehicules, $materiel, $quittances)
    {
        return $this->business->importIntervention($intervention, $sapeurs, $groupes, $missions, $appels, $vehicules, $materiel, $quittances);
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

    /**
     * Updates a intervention.
     *
     * @param int
     * @param array
     * @return Intervention
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function editInterventionInformationsById($interventionId, $data)
    {
        return $this->business->editInterventionInformationsById($interventionId, $data);
    }

    public function validerInterventionById($interventionId)
    {
        return $this->business->validerInterventionById($interventionId);
    }

    /**
     * Delete a intervention.
     *
     * @param int
     */
    public function deleteInterventionById($interventionId)
    {
        return $this->business->deleteInterventionById($interventionId);
    }

    /**
     * Ajout de sapeurs d'un intervention
     *
     * @param $data
     * @return array
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function addPresences($interventionId, $sapeurs)
    {
        $statut = $this->business->addPresences($interventionId, $sapeurs);
        return [
            "statut" => $statut,
            "sapeurs" => $this->repository->getInterventionPresences($interventionId)
        ];
    }

    /**
     * Modification de sapeurs d'une intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function updatePresences($interventionId, $sapeurs)
    {
        $this->business->updatePresences($interventionId, $sapeurs);
        return $this->repository->getInterventionPresences($interventionId);
    }

    /**
     * Suppression de sapeurs d'un intervention
     *
     * @param $data
     */
    public function removePresences($interventionId, $ids)
    {
        return $this->business->removePresences($interventionId, $ids);
    }

    /**
     * Return les statistiques de présence pour les interventions
     *
     * @param int $exerciceComptableId
     * @return array
     */
    public function statPresences(int $exerciceComptableId): array
    {
        // return InterventionSapeur::where('exercice_comptable_id', '=', $exerciceComptableId)->get();
        return DB::select("SELECT ins.*
                FROM intervention_sapeur as ins
                INNER JOIN interventions as i ON i.id = ins.intervention_id
                WHERE i.exercice_comptable_id = ?
            ", [$exerciceComptableId]);
    }

    /**
     * Ajout de phases d'une intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function addPhases($interventionId, $phases)
    {
        $this->business->addPhases($interventionId, $phases);
        return $this->repository->getInterventionPhases($interventionId);
    }

    /**
     * Modification de phases d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function updatePhases($interventionId, $phases)
    {
        $this->business->updatePhases($interventionId, $phases);
        return $this->repository->getInterventionPhases($interventionId);
    }

    /**
     * Suppression de phases d'un intervention
     *
     * @param $data
     */
    public function removePhases($interventionId, $ids)
    {
        $this->business->removePhases($interventionId, $ids);
    }

    /**
     * Ajout d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function addAppels($interventionId, $appels)
    {
        $this->business->addAppels($interventionId, $appels);
        return $this->repository->getInterventionAppels($interventionId);
    }

    /**
     * Modification d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function updateAppels($interventionId, $appels)
    {
        $this->business->updateAppels($interventionId, $appels);
        return $this->repository->getInterventionAppels($interventionId);
    }

    /**
     * Suppression d'appels d'un intervention
     *
     * @param $data
     */
    public function removeAppels($interventionId, $ids)
    {
        $this->business->removeAppels($interventionId, $ids);
    }

    /**
     * Ajout de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function addMissions($interventionId, $missions)
    {
        $this->business->addMissions($interventionId, $missions);
        return $this->repository->getInterventionMissions($interventionId);
    }

    /**
     * Modification de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function updateMissions($interventionId, $missions)
    {
        $this->business->updateMissions($interventionId, $missions);
        return $this->repository->getInterventionMissions($interventionId);
    }

    /**
     * Suppression de missions à un intervention
     *
     * @param $data
     */
    public function removeMissions($interventionId, $ids)
    {
        $this->business->removeMissions($interventionId, $ids);
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function addMateriels($interventionId, $materiels)
    {
        $this->business->addMateriels($interventionId, $materiels);
        return $this->repository->getInterventionMateriels($interventionId);
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function updateMateriels($interventionId, $materiels)
    {
        $this->business->updateMateriels($interventionId, $materiels);
        return $this->repository->getInterventionMateriels($interventionId);
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public function removeMateriels($interventionId, $ids)
    {
        $this->business->removeMateriels($interventionId, $ids);
    }

    /**
     * Ajout de quittances à un intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function addQuittances($interventionId, $quittances)
    {
        $this->business->addQuittances($interventionId, $quittances);
        return $this->repository->getInterventionQuittances($interventionId);
    }

    /**
     * Suppression de quittances à un intervention
     *
     * @param $data
     */
    public function removeQuittances($interventionId, $ids)
    {
        $this->business->removeQuittances($interventionId, $ids);
    }

    /**
     * Ajout de vehicules à un intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function addVehicules($interventionId, $vehicules)
    {
        $this->business->addVehicules($interventionId, $vehicules);
        return $this->repository->getInterventionVehicules($interventionId);
    }

    /**
     * Suppression de vehicules à un intervention
     *
     * @param $data
     */
    public function removeVehicules($interventionId, $ids)
    {
        $this->business->removeVehicules($interventionId, $ids);
    }

    /**
     * Ajout de groupes à un intervention
     *
     * @param $data
     * @return Collection
     * @throws \App\Domaine\Exceptions\ArrayException
     */
    public function addGroupes($interventionId, $groupes)
    {
        $this->business->addGroupes($interventionId, $groupes);
        return $this->repository->getInterventionGroupes($interventionId);
    }

    /**
     * Suppression de groupes à un intervention
     *
     * @param $data
     */
    public function removeGroupes($interventionId, $ids)
    {
        $this->business->removeGroupes($interventionId, $ids);
    }

    public function rapport($interventionId, $params, string $sisKey)
    {
        $withOptions = ['statFederal', 'typeIntervention', 'localite', 'chefIntervention', 'traitement'];
        $withMapping = [
            'groupes' => 'groupes',
            'presences' => 'presences',
            // 'montants' => 'boolean',
            'vehicules' => 'vehicules',
            'materiel' => 'materiels',
            'missions' => 'missions.sapeurObject',
            'appels' => 'appels',
        ];

        foreach ($params as $param => $value) {
            if ($value && array_key_exists($param, $withMapping)) {
                $withOptions[] = $withMapping[$param];
            }
        }

        // Chargement des vehicules
        $vehiculesMap = [];
        if (in_array('vehicules', $withOptions)) {
            $vehicules = Article::join('materiel_types', 'articles.materiel_type_id', '=', 'materiel_types.id')
                ->where('materiel_types.type', '=', MaterielTypeBusiness::TYPE_VEHICULE)
                ->get(['articles.*']);
            ;
            foreach ($vehicules as $vehicule) {
                $vehiculesMap[$vehicule->id] = $vehicule->designation;
            }
        }

        // Chargement du matériel
        $materielsMap = [];
        if (in_array('materiels', $withOptions)) {
            $materiels = Materiel::get();
            foreach ($materiels as $materiel) {
                $materielsMap[$materiel->id] = $materiel->designation;
            }
        }

        // Chargement des groupes
        $groupesMap = [];
        if (in_array('groupes', $withOptions)) {
            $groupes = Groupe::get();
            foreach ($groupes as $groupe) {
                $groupesMap[$groupe->id] = $groupe;
            }
        }

        // Chargement des groupes
        $ecritures = [];
        if (isset($params['montants']) && $params['montants']) {
            $ecritures = Ecriture::where('intervention_id', '=', $interventionId)
                ->groupBy('sapeur_id')
                ->selectRaw('sum(total) as total, sapeur_id')
                ->pluck('total', 'sapeur_id')
                ->toArray();

            $total = array_sum(array_map(fn($e) => floatval($e), array_values($ecritures)));
            $ecritures['total'] = number_format($total, 2);
        }

        $intervention = Intervention::with($withOptions)->find($interventionId);

        // Chargement des sapeurs et quittances
        $sapeursMap = [];
        $quittancesMap = [];
        $presences = [];
        if (in_array('presences', $withOptions)) {
            $sapeurs = Sapeur::get(['nom', 'prenom', 'id']);
            foreach ($sapeurs as $sapeur) {
                $sapeursMap[$sapeur->id] = $sapeur->toArray();
            }

            foreach ($intervention->presences as $presence) {
                if (!array_key_exists('presences', $sapeursMap[$presence->sapeur_id])) {
                    $sapeursMap[$presence->sapeur_id]['presences'] = [];
                }
                $sapeursMap[$presence->sapeur_id]['presences'][] = $presence;
            }

            // Chargement des quittances
            $quittances = Quittance::where('intervention_id', $interventionId)->get();
            foreach ($quittances as $quittance) {
                $quittancesMap[$quittance->sapeur_id] = $quittance;
            }

            $presences = array_filter($sapeursMap, function ($s) {
                return array_key_exists('presences', $s);
            });
            //TODO: Trier par nom, prénom
        }

        $logoPath = (new SisParamBusiness())->getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::RapportIntervention,
            [
                "intervention" => $intervention,
                "params" => $params,
                "vehicules" => $vehiculesMap,
                "materiels" => $materielsMap,
                "groupes" => $groupesMap,
                "sapeurs" => $sapeursMap,
                "quittances" => $quittancesMap,
                "presences" => $presences,
                "ecritures" => $ecritures,
            ],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'rapport-intervention.pdf'
        );
    }

    /**
     * Return le nombre d'intervention par materiel pour l'année comptable
     *
     * @param int $exerciceComptableId
     * @return array
     */
    public function statMateriel(int $exerciceComptableId)
    {
        return DB::select("SELECT im.materiel_id, sum(im.quantite) as nb
                FROM intervention_materiel as im
                INNER JOIN interventions as i ON i.id = im.intervention_id
                WHERE i.exercice_comptable_id = ?
                GROUP BY im.materiel_id
            ", [$exerciceComptableId]);
    }

    /**
     * Return le nombre d'intervention par véhicule pour l'année comptable
     *
     * @param int $exerciceComptableId
     * @return array
     */
    public function statVehicule(int $exerciceComptableId)
    {
        return DB::select("SELECT iv.vehicule_id, sum(i.stat_nb) as nb
                FROM intervention_vehicule as iv
                INNER JOIN interventions as i ON i.id = iv.intervention_id
                WHERE i.exercice_comptable_id = ?
                GROUP BY iv.vehicule_id
            ", [$exerciceComptableId]);
    }

    /**
     * Return le nombre d'intervention par véhicule pour l'année comptable
     *
     * @param int $exerciceComptableId
     * @return array
     */
    public function statTypeIntervention($exerciceComptableId)
    {
        return DB::select("SELECT t.id, COUNT(DISTINCT i.id) AS nb, SUM(TIMESTAMPDIFF(MINUTE, isa.debut, isa.fin) / 60) AS heures
                FROM type_interventions AS t
                INNER JOIN interventions AS i ON i.type_intervention_id = t.id
                LEFT OUTER JOIN intervention_sapeur AS isa ON i.id = isa.intervention_id
                WHERE i.exercice_comptable_id = ?
                GROUP BY t.id;
            ", [$exerciceComptableId]);
    }

    /**
     * Return le nombre d'intervention par stat federal pour l'année comptable
     *
     * @param int $exerciceComptableId
     * @return Response
     */
    public function statFederal($exerciceComptableId)
    {
        return DB::select("SELECT s.id, COUNT(DISTINCT i.id) AS nb, SUM(TIMESTAMPDIFF(MINUTE, isa.debut, isa.fin) / 60) AS heures
                FROM stat_federals AS s
                INNER JOIN interventions AS i ON i.stat_federal_id = s.id
                LEFT OUTER JOIN intervention_sapeur AS isa ON i.id = isa.intervention_id
                WHERE i.exercice_comptable_id = ?
                GROUP BY s.id;
            ", [$exerciceComptableId]);
    }

    /**
     * Return le nombre d'intervention par traitement pour l'année comptable
     *
     * @param int $exerciceComptableId
     * @return Response
     */
    public function statTraitement($exerciceComptableId)
    {
        return DB::select("SELECT i.intervention_traitement_id AS id, COUNT(i.id) as nb
                FROM interventions as i
                WHERE i.exercice_comptable_id = ?
                GROUP BY i.intervention_traitement_id;
            ", [$exerciceComptableId]);
    }

    /**
     * Return le nombre d'intervention par heures horaires
     *
     * @param int $exerciceComptableId
     * @return Response
     */
    public function statHeuresHoraire($exerciceComptableId)
    {
        // TODO:
        // (durée modulo la période voulue) * (nb heures passées)
        $presences = DB::select("SELECT isa.debut, TIMESTAMPDIFF(MINUTE, isa.debut, isa.fin) / 60 AS heures
                FROM interventions AS i
                FROM intervention_sapeur AS isa ON i.id = isa.intervention_id
                WHERE i.exercice_comptable_id = ?;
            ", [$exerciceComptableId]);

        // TODO: Compute stats
        foreach ($presences as $presence) {
            // TODO:
        }
    }

    /**
     * Return le nombre d'intervention par heures journalier
     *
     * @param int $exerciceComptableId
     * @return Response
     */
    public function statHeuresJournalier($exerciceComptableId)
    {
        // TODO:
        return DB::select("SELECT i.intervention_traitement_id AS id, COUNT(i.id) as nb
                FROM interventions as i
                WHERE i.exercice_comptable_id = ?
                GROUP BY i.intervention_traitement_id;
            ", [$exerciceComptableId]);
    }

    /**
     * Return le nombre d'intervention par heures mensuel
     *
     * @param int $exerciceComptableId
     * @return Response
     */
    public function statHeuresMensuel($exerciceComptableId)
    {
        // TODO:
        return DB::select("SELECT i.intervention_traitement_id AS id, COUNT(i.id) as nb
                FROM interventions as i
                WHERE i.exercice_comptable_id = ?
                GROUP BY i.intervention_traitement_id;
            ", [$exerciceComptableId]);
    }
}
