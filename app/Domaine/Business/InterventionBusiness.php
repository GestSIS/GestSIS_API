<?php

namespace App\Domaine\Business;

use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Appel;
use App\Models\Article;
use App\Models\Ecriture;
use App\Models\ExerciceComptable;
use App\Models\Groupe;
use App\Models\GroupeIntervention;
use App\Models\Intervention;
use App\Models\InterventionMateriel;
use App\Models\InterventionSapeur;
use App\Models\InterventionVehicule;
use App\Models\Materiel;
use App\Models\Mission;
use App\Models\Phase;
use App\Models\Quittance;
use App\Models\Sapeur;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class InterventionBusiness
{

    public const INTERVENTION_STATUT_EMPTY = 0;
    public const INTERVENTION_STATUT_SAISI = 1;
    public const INTERVENTION_STATUT_VALIDE = 2;
    public const INTERVENTION_STATUT_IMPUTE = 3;

    private static function checkIsNotImpute($interventionId)
    {
        $statut = Intervention::findOrFail($interventionId)->statut;
        if ($statut >= self::INTERVENTION_STATUT_IMPUTE) {
            throw new ArrayException([], 'Intervention already impute');
        }
    }

    /**
     * Create a intervention
     *
     * @param $data
     * @return Intervention
     * @throws ArrayException
     */
    public static function createIntervention($data): Intervention
    {
        //TODO Vérifier intervention comptable
        $phaseTypeIntervention = 1;
        $data['statut'] = self::INTERVENTION_STATUT_EMPTY;

        $data['lieu'] ??= '';
        $data['agent'] ??= '';
        $data['description'] ??= '';
        $data['proprietaire'] ??= '';
        $data['responsable'] ??= '';
        $data['wgs84'] ??= '';

        $intervention = new Intervention();
        $intervention->fill($data);
        $intervention->date_imputation = null;
        $intervention->exercice_comptable_id = $data['exercice_comptable_id'];
        $intervention->save();

        $phase = new Phase();
        $phase->debut = null;
        $phase->phase_type_id = $phaseTypeIntervention;
        $phase->intervention_id = $intervention->id;
        $phase->save();

        return $intervention;
    }

    /**
     * Import an intervention
     *
     * @param $data
     * @return InterventionBusiness
     * @throws ArrayException
     */
    public static function importIntervention($intervention, $sapeurs, $groupes, $missions, $appels, $vehicules, $materiel, $quittances)
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
        if ($exerciceComptable === null && $anneeEnCours == Carbon::parse($intervention['date_debut'])->year) {
            // Création de l'exercice comptable
            $exerciceComptable = new ExerciceComptable();
            $exerciceComptable->annee = $anneeEnCours;
            $exerciceComptable->designation = "Année comptable $anneeEnCours";
            $exerciceComptable->debut = Carbon::createFromDate($anneeEnCours, 1, 1);
            $exerciceComptable->fin = Carbon::createFromDate($anneeEnCours, 12, 31);
            $exerciceComptable->boucle = false;
            $exerciceComptable->save();
        }

        // Check pas déjà cloturé
        if ($exerciceComptable === null) {
            throw new ArrayException(["message" => "Exercice comptable inexistant"]);
        } elseif ($exerciceComptable->boucle) {
            throw new ArrayException(["message" => "Exercice comptable déjà bouclé"]);
        }

        $intervention['exercice_comptable_id'] = $exerciceComptable->id;
        $intervention['wgs84'] ??= '';
        $intervention['lieu'] ??= '';
        $intervention['agent'] ??= '';
        $intervention['description'] ??= '';
        $intervention['proprietaire'] ??= '';
        $intervention['responsable'] ??= '';

        $newIntervention = new Intervention();
        $newIntervention->fill($intervention);
        $newIntervention->date_imputation = null;
        $newIntervention->exercice_comptable_id = $intervention['exercice_comptable_id'];
        $newIntervention->save();

        // Pour le moment pas de gestion des phases dans GestSIS Mobile
        $phase = new Phase();
        $phase->debut = null;
        $phase->phase_type_id = $phaseTypeIntervention;
        $phase->intervention_id = $newIntervention->id;
        $phase->save();

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
            $e['resume'] ??= '';
            $e['sapeur_id'] ??= null;
            $e['sapeur'] ??= null;
            $e['intervention_id'] = $newIntervention->id;
            return $e;
        }, $missions);

        $newIntervention->missions()->insert($missions);

        // Ajout des appels
        $appels = array_map(function ($e) use ($newIntervention) {
            $e['commentaire'] ??= '';
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
    public static function validerInterventionById($interventionId)
    {
        $intervention = Intervention::findOrFail($interventionId);
        if ($intervention->statut === self::INTERVENTION_STATUT_SAISI) {
            $intervention->update(['statut' => self::INTERVENTION_STATUT_VALIDE]);
            return $intervention->statut;
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
    public static function editInterventionInformationsById($interventionId, $data)
    {
        $data['lieu'] ??= '';
        $data['agent'] ??= '';
        $data['description'] ??= '';
        $data['proprietaire'] ??= '';
        $data['responsable'] ??= '';
        $data['wgs84'] ??= '';

        $intervention = Intervention::find($interventionId);
        $intervention->update($data);
        //TODO Update phase debut
        //TODO Check if date debut changed -> update first phase

        return $intervention;
    }

    /**
     * Delete a intervention.
     *
     * @param int
     */
    public static function deleteInterventionById($interventionId)
    {
        self::checkIsNotImpute($interventionId);
        InterventionSapeur::where('intervention_id', '=', $interventionId)->delete();
        GroupeIntervention::where('intervention_id', '=', $interventionId)->delete();
        InterventionVehicule::where('intervention_id', '=', $interventionId)->delete();
        InterventionMateriel::where('intervention_id', '=', $interventionId)->delete();
        Quittance::where('intervention_id', '=', $interventionId)->delete();
        Mission::where('intervention_id', '=', $interventionId)->delete();
        Appel::where('intervention_id', '=', $interventionId)->delete();
        Phase::where('intervention_id', '=', $interventionId)->delete();
        Intervention::destroy($interventionId);
        return true;
    }

    /**
     * Ajout de sapeurs d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function addPresences($interventionId, $sapeurs)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($sapeurs as $sapeur) {
            // TODO: Check duplicated period of time

            $sap = new InterventionSapeur();
            $sap->fill($sapeur);
            $sap->sapeur_id = $sapeur['sapeur_id'];
            $sap->intervention_id = $interventionId;
            $sap->save();
        }

        $intervention = Intervention::findOrFail($interventionId);
        if ($intervention->statut < self::INTERVENTION_STATUT_SAISI) {
            $intervention->update(['statut' => self::INTERVENTION_STATUT_SAISI]);
        }
        return $intervention->statut;
    }

    /**
     * Modification de sapeurs d'une intervention
     *
     * @param $data
     * @return Collection
     */
    public static function updatePresences($interventionId, $sapeurs)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($sapeurs as $sapeur) {
            // TODO: Check period non dupliqué

            InterventionSapeur::where('intervention_id', $interventionId)
                ->whereId($sapeur['id'])
                ->update($sapeur);
        }
    }

    /**
     * Suppression de sapeurs d'un intervention
     *
     * @param $data
     */
    public static function removePresences($interventionId, $ids)
    {
        self::checkIsNotImpute($interventionId);

        InterventionSapeur::where('intervention_id', $interventionId)
            ->whereIn('id', $ids)
            ->delete();

        $presences = InterventionSapeur::where('intervention_id', $interventionId)->get();
        if ($presences->isEmpty()) {
            $intervention = Intervention::findOrFail($interventionId);
            $intervention->update(['statut' => self::INTERVENTION_STATUT_EMPTY]);
        }
        return true;
    }

    /**
     * Ajout d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function addAppels($interventionId, $appels)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($appels as $appel) {
            $appel['commentaire'] ??= '';

            $app = new Appel();
            $app->fill($appel);
            $app->intervention_id = $interventionId;
            $app->save();
        }
    }

    /**
     * Modification d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function updateAppels($interventionId, $appels)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($appels as $appel) {
            $appel['commentaire'] ??= '';

            Appel::where('intervention_id', $interventionId)
                ->whereId($appel['id'])
                ->update($appel);
        }
    }

    /**
     * Suppression d'appels d'une intervention
     *
     * @param $data
     */
    public static function removeAppels($interventionId, $ids)
    {
        self::checkIsNotImpute($interventionId);

        Appel::where('intervention_id', $interventionId)
            ->whereIn('id', $ids)
            ->delete();
    }

    /**
     * Ajout de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function addMissions($interventionId, $missions)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($missions as $mission) {
            $mission['resume'] ??= '';

            $mis = new Mission();
            $mis->fill($mission);
            $mis->intervention_id = $interventionId;
            $mis->save();
        }
    }

    /**
     * Modification de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function updateMissions($interventionId, $missions)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($missions as $mission) {
            $mission['resume'] ??= '';

            Mission::where('intervention_id', $interventionId)
                ->whereId($mission['id'])
                ->update($mission);
        }
    }

    /**
     * Suppression de missions à un intervention
     *
     * @param $data
     */
    public static function removeMissions($interventionId, $ids)
    {
        self::checkIsNotImpute($interventionId);

        Mission::where('intervention_id', $interventionId)
            ->whereIn('id', $ids)
            ->delete();
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function addPhases($interventionId, $phases)
    {
        self::checkIsNotImpute($interventionId);

        $intervention = Intervention::find($interventionId);
        $existingPhases = Phase::where('intervention_id', $interventionId)->get();

        $debut = Carbon::parse($intervention->date_debut . " " . $intervention->heure_debut);
        foreach ($phases as $phase) {
            if ($debut >= Carbon::parse($phase['debut'])) {
                throw new ArrayException(["debut" => "Debut trop tôt"]);
            }

            foreach ($existingPhases as $existingPhase) {
                if ($existingPhase->debut !== null && $debut === Carbon::parse($existingPhase->debut)) {
                    throw new ArrayException(["debut" => "Duplicated phase at same time"]);
                }
            }
            $newPhase = new Phase();
            $newPhase->fill($phase);
            $newPhase->intervention_id = $interventionId;
            $newPhase->save();
        }
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function updatePhases($interventionId, $phases)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($phases as $phase) {
            Phase::where('intervention_id', $interventionId)
                ->whereId($phase['id'])
                ->update($phase);
        }
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public static function removePhases($interventionId, $ids)
    {
        self::checkIsNotImpute($interventionId);

        Phase::where('intervention_id', $interventionId)
            ->whereIn('id', $ids)
            ->delete();
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function addMateriels($interventionId, $materiels)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($materiels as $materiel) {
            $mat = new InterventionMateriel();
            $mat->fill($materiel);
            $mat->materiel_id = $materiel['materiel_id'];
            $mat->intervention_id = $interventionId;
            $mat->save();
        }
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function updateMateriels($interventionId, $materiels)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($materiels as $materiel) {
            InterventionMateriel::where('intervention_id', $interventionId)
                ->whereId($materiel['id'])
                ->update(['quantite' => $materiel['quantite']]);
        }
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public static function removeMateriels($interventionId, $ids)
    {
        self::checkIsNotImpute($interventionId);

        InterventionMateriel::where('intervention_id', $interventionId)
            ->whereIn('id', $ids)
            ->delete();
    }

    /**
     * Ajout de quittances à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function addQuittances($interventionId, $quittances)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($quittances as $quittance) {
            $newQuittance = new Quittance();
            $newQuittance->sapeur_id = $quittance;
            $newQuittance->intervention_id = $interventionId;
            $newQuittance->save();
        }
    }

    /**
     * Suppression de quittances à un intervention
     *
     * @param $data
     */
    public static function removeQuittances($interventionId, $ids)
    {
        self::checkIsNotImpute($interventionId);

        Quittance::where('intervention_id', $interventionId)
            ->whereIn('id', $ids)
            ->delete();
    }

    /**
     * Ajout de vehicules à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function addVehicules($interventionId, $vehicules)
    {
        self::checkIsNotImpute($interventionId);

        //Check duplicated vehicules
        $vehiculeIds = InterventionVehicule::where('intervention_id', $interventionId)
            ->pluck('vehicule_id')->toArray();
        $vehicules = array_diff($vehicules, $vehiculeIds);

        foreach ($vehicules as $vehicule) {
            $newVehicule = new InterventionVehicule();
            $newVehicule->vehicule_id = $vehicule;
            $newVehicule->intervention_id = $interventionId;
            $newVehicule->save();
        }
    }

    /**
     * Suppression de vehicules à un intervention
     *
     * @param $data
     */
    public static function removeVehicules($interventionId, $ids)
    {
        self::checkIsNotImpute($interventionId);

        InterventionVehicule::where('intervention_id', $interventionId)
            ->whereIn('vehicule_id', $ids)
            ->delete();
    }

    /**
     * Ajout de groupes à une intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public static function addGroupes($interventionId, $groupes)
    {
        self::checkIsNotImpute($interventionId);

        foreach ($groupes as $groupe) {
            $newGroupe = new GroupeIntervention();
            $newGroupe->no = $groupe['no'];
            $newGroupe->designation = $groupe['designation'];
            $newGroupe->intervention_id = $interventionId;
            $newGroupe->save();
        }
    }

    /**
     * Suppression de groupes à un intervention
     *
     * @param $data
     */
    public static function removeGroupes($interventionId, $ids)
    {
        self::checkIsNotImpute($interventionId);

        GroupeIntervention::where('intervention_id', $interventionId)
            ->whereIn('id', $ids)
            ->delete();
    }

    public static function rapport($interventionId, $params, string $sisKey)
    {
        $withOptions = ['statFederal', 'typeIntervention', 'localite', 'chefIntervention', 'traitement'];
        $withMapping = [
            'groupes' => 'groupes',
            'presences' => 'presences',
            'presencesResume' => 'presences',
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

        $vehiculesMap = [];
        if (in_array('vehicules', $withOptions)) {
            $vehiculesMap = Article::join('materiel_types', 'articles.materiel_type_id', '=', 'materiel_types.id')
                ->where('materiel_types.type', '=', MaterielTypeBusiness::TYPE_VEHICULE)
                ->pluck('articles.designation', 'articles.id')
                ->toArray();
        }

        $materielsMap = [];
        if (in_array('materiels', $withOptions)) {
            $materielsMap = Materiel::pluck('designation', 'id')->toArray();
        }

        $groupesMap = [];
        if (in_array('groupes', $withOptions)) {
            $groupesMap = Groupe::all()->keyBy('id')->toArray();
        }

        $ecritures = [];
        if (isset($params['montants']) && $params['montants']) {
            $ecritures = Ecriture::where('intervention_id', '=', $interventionId)
                ->groupBy('sapeur_id')
                ->selectRaw('sum(total) as total, sapeur_id')
                ->pluck('total', 'sapeur_id')
                ->toArray();

            $total = array_sum(array_map(floatval(...), array_values($ecritures)));
            $ecritures['total'] = number_format($total, 2);
        }

        $intervention = Intervention::with($withOptions)->find($interventionId);

        $sapeursMap = [];
        $quittancesMap = [];
        $presences = [];
        if (in_array('presences', $withOptions) || in_array('presencesResume', $withOptions)) {
            $sapeurs = Sapeur::get(['nom', 'prenom', 'id']);
            foreach ($sapeurs as $sapeur) {
                $sapeursMap[$sapeur->id] = $sapeur->toArray();
            }

            foreach ($intervention->presences as $presence) {
                $sapeursMap[$presence->sapeur_id]['presences'] ??= [];
                $sapeursMap[$presence->sapeur_id]['presences'][] = $presence;
            }

            $quittances = Quittance::where('intervention_id', $interventionId)->get();
            foreach ($quittances as $quittance) {
                $quittancesMap[$quittance->sapeur_id] = $quittance;
            }

            $presences = collect($sapeursMap)->filter(fn($s) => isset($s['presences']))->all();
        }

        $logoPath = SisParamBusiness::getLogo($sisKey);
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
}
