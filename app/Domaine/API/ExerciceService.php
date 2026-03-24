<?php

namespace App\Domaine\API;

use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\Business\SisParamBusiness;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\ExcuseType;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\Fonction;
use App\Infrastructure\Models\HeureExercice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExerciceService
{
    protected $repository;
    protected $sapeurRepository;
    protected $business;

    public function __construct(ExerciceRepository $repository, SapeurRepository $sapeurRepository, ExerciceBusiness $business)
    {
        $this->repository = $repository;
        $this->sapeurRepository = $sapeurRepository;
        $this->business = $business;
    }

    public function listeExercice(int $exerciceComptableId)
    {
        return $this->repository->listExerciceLight($exerciceComptableId);
    }

    public function absences($exerciceComptableId)
    {
        return ExerciceSapeur::join('exercices', 'exercices.id', '=', 'exercice_sapeur.exercice_id')
            ->join('exercice_categories', 'exercices.exercice_categorie_id', '=', 'exercice_categories.id')
            ->where('exercices.exercice_comptable_id', '=', $exerciceComptableId)
            ->where('exercices.date', '<=', Carbon::now())
            ->where('exercice_categories.amendable', '=', True)
            ->where('exercices.statut', '<>', ExerciceBusiness::EXERCICE_STATUT_ANNULE)
            ->where(function ($q) {
                $q->where('exercice_sapeur.present', '=', 0)
                    ->where('exercice_sapeur.convoque', '=', 1)
                    ->where('exercice_sapeur.remplace', '=', 0)
                    ->where('exercice_sapeur.absent', '=', 1)
                    ->orWhereNotNull('exercice_sapeur.excuse_type_id');
            })
            ->select('exercice_sapeur.*')
            ->get()->toArray();
    }

    public function listExerciceOfSapeurById($exerciceComptableId, $sapeurId)
    {
        return $this->repository->listExerciceOfSapeurById($exerciceComptableId, $sapeurId);
    }

    public function listeSapeurOfExerciceById($exerciceId, $hasPresencePermission = false)
    {
        $champs = [
            'id',
            'created_at',
            'updated_at',
            'sapeur_id',
            'exercice_id',
            'excuse_type_id',
            'convoque',
            'present',
            'remplace',
            'absent',
            'excuse_statut',
            'date_demande',
            'justificatif_path',
            'date_validation',
        ];
        $heures = HeureExercice
            ::where('exercice_id', $exerciceId)
            ->get()->toArray();
        $sapeurs = ExerciceSapeur
            ::where('exercice_id', $exerciceId)
            ->get($hasPresencePermission ? '*' : $champs)->toArray();

        $dictionary = [];
        foreach ($sapeurs as $sapeur) {
            $dictionary[$sapeur['sapeur_id']] = $sapeur;
            $dictionary[$sapeur['sapeur_id']]['heures'] = [];
        }
        foreach ($heures as $heure) {
            if (!array_key_exists($heure['sapeur_id'], $dictionary)) {
                $dictionary[$heure['sapeur_id']] = [
                    'convoque' => False,
                    'present' => False,
                    'absent' => False,
                    'remplace' => False,
                    'excuse_type_id' => null,
                    'heures' => [],
                ];
            }
            $dictionary[$heure['sapeur_id']]['heures'][] = $heure;
        }
        return array_values($dictionary);
    }

    public function sapeurOfExerciceById($exerciceId, $sapeurId)
    {
        $heures = HeureExercice
            ::where('exercice_id', $exerciceId)
            ->where('sapeur_id', $sapeurId)
            ->get()->toArray();
        $sapeur = ExerciceSapeur
            ::where('exercice_id', $exerciceId)
            ->where('sapeur_id', $sapeurId)
            ->first()->toArray();

        if (!$sapeur) {
            $sapeur = [
                'convoque' => False,
                'present' => False,
                'absent' => False,
                'remplace' => False,
                'excuse_type_id' => null,
                'heures' => [],
            ];
        }
        $sapeur['heures'] = $heures;

        return $sapeur;
    }

    /**
     * Ajout de sapeurs à un exercice
     *
     * @param $sapeurs
     * @throws ArrayException
     * @return array
     */
    public function addSapeurs($exerciceId, $sapeurs)
    {
        $statut = $this->business->addSapeurs($exerciceId, $sapeurs);
        return [
            "statut" => $statut,
            "sapeurs" => $this->listeSapeurOfExerciceById($exerciceId)
        ];
    }

    /**
     * Modification de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public function updateSapeurs($exerciceId, $sapeurs, $hasValidationPermission)
    {
        $statut = $this->business->updateSapeurs($exerciceId, $sapeurs, $hasValidationPermission);
        return [
            'statut' => $statut,
            'sapeurs' => $this->listeSapeurOfExerciceById($exerciceId)
        ];
    }

    /**
     * Modification d'une excuse
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public function updateExcuse($exerciceId, $sapeurs, $hasValidationPermission)
    {
        $statut = $this->business->updateSapeurs($exerciceId, $sapeurs, $hasValidationPermission);
        return [
            'statut' => $statut,
            'sapeurs' => $this->listeSapeurOfExerciceById($exerciceId)
        ];
    }

    /**
     * Suppression d'une excuse
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public function removeExcuse($sapeurId, $exerciceId, $hasValidationPermission)
    {
        return $this->business->removeExcuse($sapeurId, $exerciceId, $hasValidationPermission);
    }

    /**
     * Modification des présences d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public function updatePresences($exerciceId, $sapeurs)
    {
        $statut = $this->business->updatePresences($exerciceId, $sapeurs);
        return [
            'statut' => $statut,
            'sapeurs' => $this->listeSapeurOfExerciceById($exerciceId)
        ];
    }


    /**
     * Modification d'une présence d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public function updatePresence($presenceId, $presence, $file, $hasValidationPermission, $sisKey)
    {
        $statut = $this->business->updatePresence($presenceId, $presence, $file, $hasValidationPermission, $sisKey);
        $exerciceSapeur = ExerciceSapeur::with('exercice')->find($presenceId);
        return [
            'statut' => $statut,
            'sapeur' => $this->sapeurOfExerciceById($exerciceSapeur->exercice_id, $exerciceSapeur->sapeur_id),
        ];
    }

    /**
     * Modification des présences pour un sapeur
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public function updateSapeurPresences($exercices, $hasValidationPermission)
    {
        return $this->business->updateSapeurPresences($exercices, $hasValidationPermission);
    }

    /**
     * Suppression de sapeurs d'un exercice
     *
     * @param $data
     * @return statut
     */
    public function removeSapeurs($exerciceId, $ids)
    {
        return $this->business->removeSapeurs($exerciceId, $ids);
    }

    public function createHeure($heure, $hasValidationPermission)
    {
        return $this->business->createHeure($heure, $hasValidationPermission);
    }

    public function updateHeure($heureId, $heure, $hasValidationPermission)
    {
        return $this->business->updateHeure($heureId, $heure, $hasValidationPermission);
    }

    public function removeHeure($heureId, $hasValidationPermission)
    {
        return $this->business->removeHeure($heureId, $hasValidationPermission);
    }

    public function supprimerConvocations($sapeurId, $exerciceSapeursIds)
    {
        return $this->business->supprimerConvocations($sapeurId, $exerciceSapeursIds);
    }

    public function ajouterHeureExercice($exerciceId, $data)
    {
        return $this->business->ajouterHeureExercice($exerciceId, $data);
    }

    public function modifierHeureExercice($exerciceId, $id, $data)
    {
        return $this->business->modifierHeureExercice($exerciceId, $id, $data);
    }

    public function supprimerHeureExercice($exerciceId, $id)
    {
        return $this->business->supprimerHeureExercice($exerciceId, $id);
    }

    function listeAppel($exerciceId, string $sisKey)
    {
        $exercice = Exercice::with(['sapeurs', 'localite'])->findOrFail($exerciceId)->toArray();
        $sapeurs = $this->sapeurRepository->listeSapeurLight();
        $exercice['sapeurs'] = array_map(function ($s) use ($sapeurs) {
            $id = $s['sapeur_id'];
            $sap = array_values(array_filter($sapeurs, function ($sapeur) use ($id) {
                return $sapeur['id'] == $id;
            }))[0];
            $s['display'] = $sap['nom_prenom'];
            $s['fonction_id'] = $sap['fonction_id'] ?? 0;
            return $s;
        }, array_values($exercice['sapeurs']));

        // Tri des sapeurs par ordre alphabétique
        usort($exercice['sapeurs'], function ($a, $b) {
            return strcmp($a['display'], $b['display']);
        });

        // Chargement des excuses types
        $excuses = ExcuseType::get();
        $excusesMap = [];
        foreach ($excuses as $excuse) {
            $excusesMap[$excuse->id] = $excuse->designation;
        }

        // Chargement des fonctions
        $fonctions = Fonction::get();
        $fonctionsMap = [];
        foreach ($fonctions as $fonction) {
            $fonctionsMap[$fonction->id] = $fonction->nom;
        }

        $logoPath = (new SisParamBusiness())->getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::ListeAppel,
            ["exercice" => $exercice, "fonctions" => $fonctionsMap, "excuses" => $excusesMap],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'liste-appel.pdf'
        );
    }

    function listePresence($exerciceId, string $sisKey)
    {
        $exercice = Exercice::with(['sapeurs', 'localite'])->findOrFail($exerciceId)->toArray();
        $sapeurs = $this->sapeurRepository->listeSapeurLight();
        $exercice['sapeurs'] = array_map(function ($s) use ($sapeurs) {
            $id = $s['sapeur_id'];
            $sap = array_values(array_filter($sapeurs, function ($sapeur) use ($id) {
                return $sapeur['id'] == $id;
            }))[0];
            $s['display'] = $sap['nom_prenom'];
            return $s;
        }, array_values($exercice['sapeurs']));

        // Tri des sapeurs par ordre alphabétique
        usort($exercice['sapeurs'], function ($a, $b) {
            return strcmp($a['display'], $b['display']);
        });

        // Chargement des excuses types
        $excuses = ExcuseType::get();
        $excusesMap = [];
        foreach ($excuses as $excuse) {
            $excusesMap[$excuse->id] = $excuse->designation;
        }

        $logoPath = (new SisParamBusiness())->getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::ListePresence,
            ["exercice" => $exercice, "excuses" => $excusesMap],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'liste-presence.pdf'
        );
    }

    /**
     * Return les statistiques de présence pour les exercices
     *
     * @param Request $request
     * @param int $exercice_comptable_id
     * @return Response
     */
    public function statPresences(int $exercice_comptable_id)
    {
        $presences = DB::select("SELECT es.*
                FROM exercice_sapeur as es
                INNER JOIN exercices as e ON e.id = es.exercice_id
                WHERE e.exercice_comptable_id = ?
            ", [$exercice_comptable_id]);

        return $presences;
    }
}
