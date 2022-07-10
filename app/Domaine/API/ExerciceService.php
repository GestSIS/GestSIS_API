<?php

namespace App\Domaine\API;

use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\ExcuseType;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\Fonction;
use App\Infrastructure\Models\HeureExercice;
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

    public function getExerciceById($exerciceId)
    {
        return $this->repository->getExerciceByIdWith($exerciceId, ['sapeurs']);
    }

    public function listeExercice()
    {
        return $this->repository->listExerciceLight();
    }

    public function listExerciceOfSapeurById($exerciceComptableId, $sapeurId)
    {
        return $this->repository->listExerciceOfSapeurById($exerciceComptableId, $sapeurId);
    }

    /**
     * Create a exercice
     *
     * @param $data
     * @return ExerciceBusiness
     * @throws ArrayException
     */
    public function createExercice($data)
    {
        return $this->business->createExercice($data);
    }

    /**
     * Updates a post.
     *
     * @param int
     * @param array
     * @return Exercice
     * @throws ArrayException
     */
    public function updatExercice($exerciceId, $data)
    {
        return $this->repository->updateExercicebyId($exerciceId, $data);
    }

    public function deleteExerciceById($exerciceId)
    {
        return $this->business->deleteExerciceById($exerciceId);
    }

    public function cancelExerciceById($exerciceId)
    {
        return $this->business->cancelExerciceById($exerciceId);
    }

    public function reactivateExerciceById($exerciceId)
    {
        return $this->business->reactivateExerciceById($exerciceId);
    }

    public function validateExerciceById($exerciceId)
    {
        return $this->business->validateExerciceById($exerciceId);
    }

    public function listeSapeurOfExerciceById($exerciceId)
    {
        $heures = HeureExercice
            ::where('exercice_id', $exerciceId)
            ->get()->toArray();
        $sapeurs = ExerciceSapeur
            ::where('exercice_id', $exerciceId)
            ->get()->toArray();

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
                    'amende' => False,
                    'remplace' => False,
                    'excuse_type_id' => null,
                    'heures' => [],
                ];
            }
            $dictionary[$heure['sapeur_id']]['heures'][] = $heure;
        }
        return array_values($dictionary);
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
    public function updateSapeurs($exerciceId, $sapeurs, $hasValidationPremission)
    {
        $statut = $this->business->updateSapeurs($exerciceId, $sapeurs, $hasValidationPremission);
        return [
            'statut' => $statut,
            'sapeurs' => $this->listeSapeurOfExerciceById($exerciceId)
        ];
    }

    /**
     * Modification de sapeurs d'un exercice
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
     * Suppression de sapeurs d'un exercice
     *
     * @param $data
     * @return statut
     */
    public function removeSapeurs($exerciceId, $ids)
    {
        return $this->business->removeSapeurs($exerciceId, $ids);
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

    function listeAppel($exerciceId)
    {
        $exercice = $this->repository->getExerciceByIdWith($exerciceId, ['sapeurs', 'localite']);
        $sapeurs = $this->sapeurRepository->listeSapeurLight();
        $exercice->sapeurs = array_map(function ($s) use ($sapeurs) {
            $id = $s->sapeur_id;
            $sap = array_values(array_filter($sapeurs, function ($sapeur) use ($id) {
                return $sapeur->id == $id;
            }))[0];
            $s->display = $sap->nom . " " . $sap->prenom;
            $s->fonction_id = $sap->fonction_id;
            return $s;
        }, array_values($exercice->sapeurs));

        // Tri des sapeurs par ordre alphabétique
        usort($exercice->sapeurs, function ($a, $b) {
            return strcmp($a->display, $b->display);
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

        return View('pdf/liste-appel', ["exercice" => $exercice, "fonctions" => $fonctionsMap, "excuses" => $excusesMap]);
    }

    function listeAppelParLocalite($exerciceId)
    {
        $presences = $this->repository->listeSapeurOfExerciceById($exerciceId);

        return View('pdf/liste-appel-localite', ["presences" => $presences]);
    }

    function listePresence($exerciceId)
    {
        $exercice = $this->repository->getExerciceByIdWith($exerciceId, ['sapeurs', 'localite']);
        $sapeurs = $this->sapeurRepository->listeSapeurLight();
        $exercice->sapeurs = array_map(function ($s) use ($sapeurs) {
            $id = $s->sapeur_id;
            $sap = array_values(array_filter($sapeurs, function ($sapeur) use ($id) {
                return $sapeur->id == $id;
            }))[0];
            $s->display = $sap->nom . " " . $sap->prenom;
            return $s;
        }, array_values($exercice->sapeurs));

        // Tri des sapeurs par ordre alphabétique
        usort($exercice->sapeurs, function ($a, $b) {
            return strcmp($a->display, $b->display);
        });

        // Chargement des excuses types
        $excuses = ExcuseType::get();
        $excusesMap = [];
        foreach ($excuses as $excuse) {
            $excusesMap[$excuse->id] = $excuse->designation;
        }

        return View('pdf/liste-presence', ["exercice" => $exercice, "excuses" => $excusesMap]);
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
