<?php

namespace App\Domaine\API;

use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Domaine\Business\SapeurBusiness;
use App\Domaine\Business\SisParamBusiness;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Collections\ListeFoadExport;
use App\Infrastructure\Collections\ListeFsspExport;
use App\Infrastructure\Models\ControleMedical;
use App\Infrastructure\Models\CoursSapeur;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Models\FonctionSapeur;
use App\Infrastructure\Models\GradeSapeur;
use App\Infrastructure\Models\Mutation;
use App\Infrastructure\Models\Permis;
use App\Infrastructure\Models\Sapeur;
use App\Infrastructure\Models\SapeurTelephone;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SapeurService
{
    protected $repository;
    protected $business;

    public function __construct(SapeurRepository $repository, SapeurBusiness $business)
    {
        $this->repository = $repository;
        $this->business = $business;
    }

    public function trombinoscope(string $sisKey)
    {
        $imageDefault = 'icon/user.svg';

        $sapeurs = Sapeur::where([['actif', '=', 1], ['type', '=', SapeurBusiness::TYPE_SAPEUR]])
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(['id', 'nom', 'prenom']);

        $sapeurs = array_map(
            fn($sapeur) => [
                ...$sapeur,
                'photo' => $this->getPhotoSapeurPath($sapeur['id'], $sisKey)
            ],
            $sapeurs->toArray()
        );
        $logoPath = (new SisParamBusiness())->getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::Trombinoscope,
            [
                "sapeurs" => $sapeurs,
                "sisId" => $sisKey,
                "imageDefault" => $imageDefault,
            ],
            $logoPath,
            extraStorageFiles: [
                $imageDefault,
                ...array_filter(
                    array_map(
                        fn($s) => $s['photo'],
                        $sapeurs
                    ),
                    fn($path) => $path !== null
                )
            ]
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'trombinoscope.pdf'
        );
    }

    public function fiche($sapeurId, string $sisKey)
    {
        $sapeur = Sapeur::with(['localite', 'civilite', 'fonction', 'grade'])->find($sapeurId);

        $logoPath = (new SisParamBusiness())->getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::FicheSapeur,
            [
                "sapeur" => $sapeur,
                "fonctions" => FonctionSapeur::with('fonction')->where('sapeur_id', '=', $sapeurId)->orderBy('debut')->get(),
                "grades" => GradeSapeur::with('grade')->where('sapeur_id', '=', $sapeurId)->orderBy('date')->get(),
                "mutations" => Mutation::with('localite')->where('sapeur_id', '=', $sapeurId)->orderBy('incorporation')->get(),
                "cours" => CoursSapeur::with(['localite', 'cours'])->where('sapeur_id', '=', $sapeurId)->orderBy('date')->get(),
                "telephones" => SapeurTelephone::with(['telephoneType'])->where('sapeur_id', '=', $sapeurId)->orderBy('priorite')->get(),
                "permis" => Permis::with(['permisType'])->where('sapeur_id', '=', $sapeurId)->orderBy('date')->get(),
            ],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'fiche-sapeur.pdf'
        );
    }

    public function telephones()
    {
        return Sapeur::with('telephones')->get(['id'])->toArray();
    }

    public function listeFssp($date)
    {
        return Excel::download(new ListeFsspExport($date), 'liste_fssp.xlsx');
    }

    public function listeFoad($date)
    {
        return Excel::download(new ListeFoadExport($date), 'liste_foad.xlsx');
    }


    public function convocationSms()
    {
        return Sapeur::with('telephones')
            ->where('actif', '=', '1')
            ->get(['id', 'nom', 'prenom'])->toArray();
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
        return $this->repository->getSapeurGroupesById($sapeurId);
    }

    public function getSapeurTelephonesById(int $sapeurId)
    {
        return $this->repository->getSapeurTelephonesById($sapeurId);
    }

    public function getSapeurControlesMedicauxById(int $sapeurId)
    {
        return ControleMedical::where('sapeur_id', $sapeurId)->get();
    }

    private static $ALLOWED_PHOTO_EXTENSION = ['jpg', 'jpeg', 'png'];

    public function downloadPhotoSapeur($sapeurId, $sisKey)
    {
        foreach (self::$ALLOWED_PHOTO_EXTENSION as $extension) {
            $path = 'photos/' . $sisKey . '/' . $sapeurId . '.' . $extension;
            if (Storage::exists($path)) {
                return Storage::download($path, null, ['Response-Type' => 'arraybuffer']);
            }
        }
        return response()->json(Null);
    }

    public function getPhotoSapeurPath($sapeurId, $sisKey)
    {
        foreach (self::$ALLOWED_PHOTO_EXTENSION as $extension) {
            $path = 'photos/' . $sisKey . '/' . $sapeurId . '.' . $extension;
            if (Storage::exists($path)) {
                return $path;
            }
        }
        return null;
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
        return $image->storeAs('photos/' . $sisKey, $sapeurId . "." . $extension);
    }

    public function createSapeur($data)
    {
        return $this->business->createSapeur($data);
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

    public function statCivilite($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);

        $data = DB::select("SELECT c.id as 'civilite_id', count(DISTINCT s.id) as nb
                FROM civilites as c
                INNER JOIN sapeurs as s ON s.civilite_id = c.id
                INNER JOIN mutations as m ON m.sapeur_id = s.id
                WHERE m.incorporation < ?
                AND (
                    m.sortie IS NULL OR m.sortie > ?
                    )
                AND s.type = 0
                GROUP BY c.id
            ", [$exerciceComptable->fin, $exerciceComptable->debut]);

        return $data;
    }

    public function statFonction($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);

        $data = DB::select("SELECT f.id as 'fonction_id', count(DISTINCT s.id) as nb
                FROM fonctions as f
                INNER JOIN fonction_sapeur as fs ON fs.fonction_id = f.id
                INNER JOIN sapeurs as s ON s.id = fs.sapeur_id
                INNER JOIN mutations as m ON m.sapeur_id = s.id
                WHERE fs.debut <=  ?
                AND (
                    fs.fin IS NULL OR fs.fin >= ?
                    )
                AND m.incorporation <= ?
                AND (
                    m.sortie IS NULL OR m.sortie >= ?
                    )
                AND s.type = 0
                GROUP BY f.id
            ", [Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->debut), Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->debut)]);

        return $data;
    }

    public function statGrade($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);

        $data = DB::select("SELECT g.id as 'grade_id', count(DISTINCT gs.sapeur_id) as nb
                FROM grades as g
                INNER JOIN (
                    SELECT ROW_NUMBER() OVER (PARTITION BY gs.sapeur_id ORDER BY g2.tri DESC) 'row_number', gs.date as 'date', gs.sapeur_id as 'sapeur_id', gs.grade_id as 'grade_id'
                    FROM grade_sapeur as gs
                    INNER JOIN grades as g2 ON g2.id = gs.grade_id
                    WHERE gs.date <= ?
                ) as gs ON gs.grade_id = g.id
                INNER JOIN sapeurs as s ON s.id = gs.sapeur_id
                INNER JOIN mutations as m ON m.sapeur_id = s.id
                WHERE gs.row_number = 1
                AND m.incorporation <= ?
                AND (
                    m.sortie IS NULL OR m.sortie >= ?
                    )
                AND s.type = 0
                GROUP BY g.id
            ", [Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->debut)]);

        return $data;
    }

    public function statPermis($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);

        $data = DB::select("SELECT pt.id as 'permis_type_id', count(DISTINCT s.id) as nb
                FROM permis_types as pt
                INNER JOIN permis as p ON p.permis_type_id = pt.id
                INNER JOIN sapeurs as s ON s.id = p.sapeur_id
                INNER JOIN mutations as m ON m.sapeur_id = s.id
                WHERE p.date <= ?
                AND m.incorporation <= ?
                AND (
                    m.sortie IS NULL OR m.sortie >= ?
                    )
                AND s.type = 0
                GROUP BY pt.id
        ", [Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->fin), Carbon::parse($exerciceComptable->debut)]);

        return $data;
    }

    public function statLocalite($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);

        $data = DB::select("SELECT s.localite_id AS localite_id, count(DISTINCT s.id) as nb
                FROM sapeurs as s
                INNER JOIN mutations as m ON m.sapeur_id = s.id
                WHERE m.incorporation < ?
                AND (
                    m.sortie IS NULL OR m.sortie > ?
                    )
                AND s.type = 0
                GROUP BY s.localite_id
            ", [$exerciceComptable->fin, $exerciceComptable->debut]);

        return $data;
    }
}
