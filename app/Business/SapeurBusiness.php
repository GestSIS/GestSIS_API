<?php


namespace App\Business;

use App\Contracts\SapeurRepository;
use App\Exceptions\ArrayValidatorException;
use App\Models\SapeurTelephone;
use Validator;

class SapeurBusiness
{

    protected $repository;

    public function __construct(SapeurRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createSapeur($data)
    {
        $sapeur = $this->repository->createSapeur($data);

        //FIXME Finalise add new sapeur mutation
        $this->addMutation($sapeur->id, array(
            "localite_id" => $sapeur->localite_id,
            "debut" => $data['incorporation']
        ));
        return $sapeur;
    }

    public function updateSapeurById(int $sapeurId, $data)
    {
        return $this->repository->updateSapeurById($sapeurId, $data);
    }

    public function deleteSapeurById(int $sapeurId)
    {
        //TODO Check si données liées
        $this->repository->deleteSapeurById($sapeurId);
    }

    public function addCours(int $sapeurId, $data)
    {
        $cours = $this->repository->addCours($sapeurId, $data);

        $gradeId = $data['grade_id'];

        //Add Grade
        if ($gradeId !== null) {
            //Add grade if not already there
            $result = array_filter($this->repository->getSapeurGradesById($sapeurId),
                function ($f) use ($gradeId) {
                    return $f->grade_id === $gradeId;
                }
            );
            if (count($result) === 0) {
                $this->addGrade($sapeurId, array(
                    'grade_id' => $data['grade_id'],
                    'date' => $data['date_grade'],
                    'remarque' => ''
                ));
            }
        }

        //Edit old fonction
        if ($data['fonction_sapeur_id'] !== null) {
            $this->updateFonction($sapeurId,
                array(
                    'id' => $data['fonction_sapeur_id'],
                    'fin' => $data['date_fonction'],
                    'remarque' => ''
                )
            );
        }

        //Add Fonction
        if ($data['fonction_id'] !== null) {
            $this->addFonction($sapeurId,
                array(
                    'fonction_id' => $data['fonction_id'],
                    'debut' => $data['date_fonction'],
                    'fin' => null,
                    'remarque' => null
                )
            );
        }

        return $cours;
    }

    public function updateCours(int $sapeurId, $data)
    {
        return $this->repository->updateCours($sapeurId, $data);
    }

    public function removeCours(int $sapeurId, int $coursSapeurId)
    {
        $this->repository->removeCours($sapeurId, $coursSapeurId);
    }

    public function addGrade(int $sapeurId, $data)
    {
        $gradeId = $data['grade_id'];

        //Check si déjà présent
        $res = array_filter($this->repository->getSapeurGradesById($sapeurId),
            function ($grade) use ($gradeId) {
                return $grade->grade_id === $gradeId;
            }
        );

        if (count($res) !== 0) {
            throw new ArrayValidatorException(array('id' => "Grade déjà existant"));
        }

        $grade = $this->repository->addGrade($sapeurId, $data);
        $this->updateMainGrade($sapeurId);

        return $grade;
    }

    public function updateGrade(int $sapeurId, $data)
    {
        $grade = $this->repository->updateGrade($sapeurId, $data);
        $this->updateMainGrade($sapeurId);
        return $grade;
    }

    public function removeGrade(int $sapeurId, int $gradeSapeurId)
    {
        $this->repository->removeGrade($sapeurId, $gradeSapeurId);
        $this->updateMainGrade($sapeurId);
    }

    public function addFonction(int $sapeurId, $data)
    {
        //Check duplicated fonction during period of time
        $fonctionId = $data['fonction_id'];

        //Check si déjà présent
        $fonctions = array_filter($this->repository->getSapeurFonctionsById($sapeurId),
            function ($fonction) use ($fonctionId) {
                return $fonction->fonction_id === $fonctionId;
            }
        );

        $startDate = $data['debut'] !== null ? date($data['debut']) : null;
        $endDate = $data['fin'] !== null ? date($data['fin']) : null;

        //Check overlaps of a fonction
        foreach ($fonctions as $fonction) {
            $start = $fonction->debut;
            $end = $fonction->fin;

            if ($this->checkOverlappingPeriod($start, $end, $startDate, $endDate)) {
                throw new ArrayValidatorException([
                    "debut" => "Duplicated period",
                    "fin" => "Duplicated period",
                    "message" => "Fonction dupliquée durant une même période"
                ]);
            }
        }

        $fonction = $this->repository->addFonction($sapeurId, $data);
        $this->updateMainFonction($sapeurId);

        return $fonction;
    }

    public function updateFonction(int $sapeurId, $data)
    {
        $id = $data['id'];

        //Check si déjà présent
        $fonctions = $this->repository->getSapeurFonctionsById($sapeurId);

        //Get fonction to update
        $fonction = array_filter($fonctions,
            function ($f) use ($id) {
                return $f->id === $id;
            }
        )[0];
        $fonctionId = $fonction->fonction_id;

        $fonctions = array_filter($fonctions,
            function ($f) use ($fonctionId, $id) {
                return $f->fonction_id === $fonctionId && $f->id !== $id;
            }
        );

        //Check si déjà présent
        $startDate = null;
        $endDate = null;

        if (array_key_exists('debut', $data)) {
            $startDate = $data['debut'] !== null ? date($data['debut']) : null;
        } else {
            $endDate = $fonction->debut;
        }
        if (array_key_exists('fin', $data)) {
            $startDate = $data['fin'] !== null ? date($data['fin']) : null;
        } else {
            $endDate = $fonction->fin;
        }

        //Check overlaps of a fonction
        foreach ($fonctions as $fct) {
            $start = $fct->debut;
            $end = $fct->fin;

            if ($this->checkOverlappingPeriod($start, $end, $startDate, $endDate)) {
                throw new ArrayValidatorException([
                    'debut' => "Duplicated period",
                    'fin' => 'Duplicated period',
                ]);
            }
        }

        //Update fonction
        $fonction = $this->repository->updateFonction($sapeurId, $data);
        $this->updateMainFonction($sapeurId);
        return $fonction;
    }

    public function removeFonction(int $sapeurId, int $fonctionSapeurId)
    {
        $this->repository->removeFonction($sapeurId, $fonctionSapeurId);
        $this->updateMainFonction($sapeurId);
    }

    public function addMutation($sapeurId, $data)
    {
        //TODO Check only one not ended Mutation
        $mutation = $this->repository->addMutation($sapeurId, $data);

        //TODO Update actif statut depending of end of all mutation
        return $mutation;
    }

    public function updateMutation(int $sapeurId, $data)
    {
        //Update mutation
        return $this->repository->updateMutation($sapeurId, $data);

        //TODO Update actif statut depending of end of all mutation
    }

    /**
     * Supppression d'une mutation
     *
     * @param int $mutationId
     */
    public function removeMutation(int $sapeurId, int $mutationId)
    {
        $this->sapeur->mutations()->where('mutations.id', $mutationId)->delete();

        //TODO Update actif statut depending of end of all mutation
    }

    public function addTelephone(int $sapeurId, $data)
    {
        $telephones = $this->sapeur->telephones()->get();
        foreach ($telephones as $tel) {
            if (strcmp(
                    trim(preg_replace('/\s+/', ' ', $tel->numero)),
                    trim(preg_replace('/\s+/', ' ', $data['numero']))
                ) === 0) {
                throw new ArrayValidatorException(['numero' => 'Duplicated numero']);
            }
        }

        //Create permis
        $telephone = new SapeurTelephone();
        $telephone->fill($data);

        //Ajout du permis au sapeur
        $this->sapeur->telephones()->save($telephone);

        return $telephone;
    }

    public function updateTelephone(int $sapeurId, $data)
    {
        $telephone = $this->sapeur->telephones()->where('sapeur_telephone.id', $data['id'])->first();

        $telephones = $this->sapeur->telephones()
            ->where('sapeur_telephone.id', '!=', $data['id'])
            ->get();

        foreach ($telephones as $tel) {
            if (strcmp(
                    trim(preg_replace('/\s+/', ' ', $tel->numero)),
                    trim(preg_replace('/\s+/', ' ', $data['numero']))
                ) === 0) {
                throw new ArrayValidatorException(['numero' => 'Duplicated numero']);
            }
        }

        //Search for the telephone
        if ($telephone === null) {
            throw new ArrayValidatorException(array('id' => "Unable to find telephone"));
        } else {
            //Update telephone
            $telephone->update($data);
            $telephone->save();
        }
        return $telephone;
    }

    public function removeTelephone(int $sapeurId, int $telephoneId)
    {
        $this->sapeur->telephones()->where('sapeur_telephone.id', $telephoneId)->delete();
    }

    public function addPermis(int $sapeurId, $data)
    {
        $permisId = $data['permis_id'];
        $res = array_filter($this->repository->getSapeurPermisById($sapeurId),
            function ($p) use ($permisId) {
                return $p->permis_id === $permisId;
            }
        );

        //Check si sapeur as déjà ce permis
        if (count(res) !== 0) {
            throw new ArrayValidatorException(array('id' => "Unable to find permis"));
        }

        $permis = $this->repository->addPermis($sapeurId, $data);

        return $permis;
    }

    public function updatePermis(int $sapeurId, $data)
    {
        return $this->repository->updatePermis($sapeurId, $data);
    }

    public function removePermis(int $sapeurId, int $permisId)
    {
        $this->repository->removePermis($sapeurId, $permisId);
    }

    /* ************************************************** *
     *                  METHODES PRIVEES                  *
     * ************************************************** */

    private function checkOverlappingPeriod($start1, $end1, $start2, $end2)
    {
        return ($end1 === null && $end2 === null ||
            $end1 === null && $start1 <= $end2 ||
            $end2 === null && $end1 >= $start2 ||
            $end1 !== null && $end2 !== null && !(
                $end1 < $start2 || $end2 < $start1
            )
        );
    }

    private function updateMainFonction($sapeurId)
    {
        $maxTri = -1;
        $maxId = -1;

        //FIXME Recupérer avec fonctions pour le tri
        $fonctions = array_filter($this->repository->getSapeurFonctionsById($sapeurId, true),
            function ($fonction) {
                return $fonction->fin === null;
            }
        );

        foreach ($fonctions as $fonctionSapeur) {
            if ($fonctionSapeur->fonction->tri > $maxTri) {
                $maxId = $fonctionSapeur->fonction->id;
                $maxTri = $fonctionSapeur->fonction->tri;
            }
        }

        $this->repository->updateSapeurById($sapeurId, array(
            "fonction_id" => $maxId <= 0 ? null : $maxId
        ));
    }

    private function updateMainGrade($sapeurId)
    {
        $maxTri = -1;
        $maxId = -1;

        //FIXME Recupérer avec grades pour le tri
        $grades = $this->repository->getSapeurGradesById($sapeurId, true);

        foreach ($grades as $gradeSapeur) {
            if ($gradeSapeur->grade->tri > $maxTri) {
                $maxId = $gradeSapeur->grade->id;
                $maxTri = $gradeSapeur->grade->tri;
            }
        }

        $this->repository->updateSapeurById($sapeurId, array(
            "grade_id" => $maxId <= 0 ? null : $maxId
        ));
    }
}
