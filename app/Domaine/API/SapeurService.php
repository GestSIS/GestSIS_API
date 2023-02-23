<?php

namespace App\Domaine\API;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\SPI\ControleMedicalRepository;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Collections\ListeFsspExport;
use App\Infrastructure\Models\MaterielPersonnel;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SapeurService
{
    protected $repository;
    protected $repositoryControles;
    protected $business;

    public function __construct(SapeurRepository $repository, ControleMedicalRepository $repositoryControles, SapeurBusiness $business)
    {
        $this->repository = $repository;
        $this->repositoryControles = $repositoryControles;
        $this->business = $business;
    }

    public function listeSapeurs()
    {
        return $this->repository->listeSapeurLight();
    }

    public function telephones()
    {
        return Sapeur::with('telephones')->get(['id'])->toArray();
    }

    public function listeFssp($date)
    {
        return Excel::download(new ListeFsspExport($date), 'liste_fssp.xlsx');
    }

    public function effectif()
    {
        return Sapeur::with('telephones', 'permis', 'fonctions', 'groupes')
            ->where('actif', '=', '1')
            ->where('type', '=', SapeurBusiness::TYPE_SAPEUR)
            ->get(['id', 'nom', 'prenom', 'email', 'annee_incorporation', 'rue', 'no_rue', 'date_naissance', 'fonction_id', 'grade_id', 'civilite_id', 'localite_id'])
            ->toArray();
    }

    public function convocationSms()
    {
        return Sapeur::with('telephones')
            ->where('actif', '=', '1')
            ->get(['id', 'nom', 'prenom'])->toArray();
    }

    public function getSapeurDetailsById($sapeurid)
    {
        return $this->repository->getSapeurDetailsById($sapeurid);
    }

    public function getSapeurGradesById(int $sapeurId)
    {
        return $this->repository->getSapeurGradesById($sapeurId);
    }

    public function getSapeurFonctionsById(int $sapeurId)
    {
        return $this->repository->getSapeurFonctionsById($sapeurId);
    }

    public function getSapeurCoursById(int $sapeurId)
    {
        return $this->repository->getSapeurCoursById($sapeurId);
    }

    public function getSapeurPermisById(int $sapeurId)
    {
        return $this->repository->getSapeurPermisById($sapeurId);
    }

    public function getSapeurMutationsById(int $sapeurId)
    {
        return $this->repository->getSapeurMutationsById($sapeurId);
    }

    public function getSapeurGroupesById(int $sapeurId)
    {
        return $this->repository->getSapeurGroupesbyId($sapeurId);
    }

    public function getSapeurTelephonesById(int $sapeurId)
    {
        return $this->repository->getSapeurTelephonesById($sapeurId);
    }

    public function getSapeurControlesMedicauxById(int $sapeurId)
    {
        return $this->repositoryControles->getSapeurControlesMedicauxById($sapeurId);
    }

    public function getSapeurMaterielsById(int $sapeurId)
    {
        return MaterielPersonnel::with('materiel')->where('sapeur_id', '=', $sapeurId)->get()->toArray();
    }

    private static $ALLOWED_PHOTO_EXTENSION = ['jpg', 'jpeg', 'png'];

    public function getPhotoSapeur($sapeurId, $sisKey)
    {
        foreach (self::$ALLOWED_PHOTO_EXTENSION as $extension) {
            $path = 'photos/' . $sisKey . '/' . $sapeurId . '.' . $extension;
            if (Storage::exists($path)) {
                // return response()->json(storage_path($path));
                // return response()->file(storage_path('app/' . $path));
                return Storage::download($path, null, ['Response-Type' => 'arraybuffer']);
            }
        }
        return response()->json(Null);
    }

    public function deletePhotoSapeur($sapeurId, $sisKey)
    {
        $path = 'photos/' . $sisKey . '/' . $sapeurId . '.';
        $files = array_map(function ($extension) use ($path) {
            return $path . $extension;
        }, self::$ALLOWED_PHOTO_EXTENSION);
        Storage::delete($files);
    }

    public function uploadPhotoSapeur($image, $sapeurId, $sisKey)
    {
        //Supprime toute potentielle image précédente
        $this->deletePhotoSapeur($sapeurId, $sisKey);

        // Ajout de l'image
        $extension = strtolower($image->extension());
        return $image->storeAs('photos/' . $sisKey,  $sapeurId . "." . $extension);
    }

    public function createSapeur($data)
    {
        return $this->business->createSapeur($data);
    }

    public function createPolitique($data)
    {
        return $this->business->createPolitique($data);
    }

    public function editSapeurDetailsById($sapeurId, $data)
    {
        return $this->business->updateSapeurById($sapeurId, $data);
    }

    public function updateNonSapeurStatut($sapeurId, $data)
    {
        return $this->business->updateNonSapeurStatut($sapeurId, $data);
    }

    public function deleteSapeurById($sapeurId)
    {
        $this->business->deleteSapeurById($sapeurId);
    }

    public function addCours($sapeurId, $cours)
    {
        return $this->business->addCours($sapeurId, $cours);
    }

    public function updateCours($sapeurId, $cours)
    {
        return $this->business->updateCours($sapeurId, $cours);
    }

    public function removeCours($sapeurId, $coursId)
    {
        $this->business->removeCours($sapeurId, $coursId);
    }

    public function addGrade($sapeurId, $grade)
    {
        return $this->business->addGrade($sapeurId, $grade);
    }

    public function updateGrade($sapeurId, $grade)
    {
        return $this->business->updateGrade($sapeurId, $grade);
    }

    public function removeGrade($sapeurId, $gradeId)
    {
        return $this->business->removeGrade($sapeurId, $gradeId);
    }

    public function addFonction($sapeurId, $fonction)
    {
        return $this->business->addFonction($sapeurId, $fonction);
    }

    public function updateFonction($sapeurId, $fonction)
    {
        return $this->business->updateFonction($sapeurId, $fonction);
    }

    public function removeFonction($sapeurId, $fonctionId)
    {
        return $this->business->removeFonction($sapeurId, $fonctionId);
    }

    public function finFonctions($sapeurId, $date, $fonctionsId)
    {
        return $this->business->finFonctions($sapeurId, $date, $fonctionsId);
    }

    public function addMutation($sapeurId, $mutation)
    {
        return $this->business->addMutation($sapeurId, $mutation);
    }

    public function updateMutation($sapeurId, $mutation)
    {
        return $this->business->updateMutation($sapeurId, $mutation);
    }

    public function removeMutation($sapeurId, $mutationId)
    {
        return $this->business->removeMutation($sapeurId, $mutationId);
    }

    public function addTelephone($sapeurId, $telephone)
    {
        return $this->business->addTelephone($sapeurId, $telephone);
    }

    public function updateTelephone($sapeurId, $telephone)
    {
        return $this->business->updateTelephone($sapeurId, $telephone);
    }

    public function removeTelephone($sapeurId, $telephoneId)
    {
        $this->business->removeTelephone($sapeurId, $telephoneId);
    }

    public function addPermis($sapeurId, $permis)
    {
        return $this->business->addPermis($sapeurId, $permis);
    }

    public function updatePermis($sapeurId, $permis)
    {
        return $this->business->updatePermis($sapeurId, $permis);
    }

    public function removePermis($sapeurId, $permisId)
    {
        $this->business->removePermis($sapeurId, $permisId);
    }

    public function finGroupes($sapeurId, $groupesIds)
    {
        return $this->business->removeGroupes($sapeurId, $groupesIds);
    }
}
