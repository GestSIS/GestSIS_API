<?php

namespace App\Domaine\API;

use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\Exercice;
use Illuminate\Database\Eloquent\Collection;
use PDF;

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

    public function listSapeurOfExerciceById($exerciceId)
    {
        return $this->repository->listSapeurOfExerciceById($exerciceId);
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
        $this->business->deleteExerciceById($exerciceId);
    }

    public function validateExercice($exerciceId)
    {
        return $this->business->validateExercice($exerciceId);
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
            "sapeurs" => $this->repository->listSapeurOfExerciceById($exerciceId)
        ];
    }

    /**
     * Modification de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public function updateSapeurs($exerciceId, $sapeurs)
    {
        $this->business->updateSapeurs($exerciceId, $sapeurs);
        return $this->repository->listSapeurOfExerciceById($exerciceId);
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

    function listeAppel($exerciceId)
    {
        $presences = $this->repository->listSapeurOfExerciceById($exerciceId);

        return View('pdf/liste-appel', ["presences" => $presences]);
        $pdf = PDF::loadView('pdf/liste-appel', ["presences" => $presences]);
        return $pdf->download('invoice.pdf');
    }

    function listeAppelParLocalite($exerciceId)
    {
        $presences = $this->repository->listSapeurOfExerciceById($exerciceId);

        return View('pdf/liste-appel-localite', ["presences" => $presences]);
        $pdf = PDF::loadView('pdf/decomptes-sapeurs', ["presences" => $presences]);
        return $pdf->download('invoice.pdf');
    }

    function listePresence($exerciceId)
    {
        $exercice = $this->repository->getExerciceByIdWith($exerciceId, ['sapeurs', 'localite']);
        $sapeurs = $this->sapeurRepository->listeSapeurLight();
        $exercice->sapeurs = array_map(function($s) use($sapeurs) {
            $id = $s->sapeur_id;
            $sap = array_values(array_filter($sapeurs, function($sapeur) use ($id) {
              return $sapeur->id == $id;
            }))[0];
            $s->display = $sap->nom." ".$sap->prenom;
            return $s;
          }, array_values($exercice->sapeurs));
          
        return View('pdf/liste-presence', ["exercice" => $exercice]);
        $pdf = PDF::loadView('pdf/liste-presence', ["exercice" => $exercice]);
        return $pdf->download('invoice.pdf');
    }
}
