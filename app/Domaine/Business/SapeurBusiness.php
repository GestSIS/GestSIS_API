<?php


namespace App\Domaine\Business;

use App\Domaine\SPI\SapeurRepository;
use App\Domaine\Exceptions\ArrayException;
use Carbon\Carbon;

class SapeurBusiness
{

    protected $repository;

    public function __construct(SapeurRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createSapeur($data)
    {
        //TODO Add iban statut système validation
        //TODO Add no_avs validation
        $data['iban_statut'] = 1;
        $data['actif'] = 1;
        $data['porteur'] = 0;
        $sapeur = $this->repository->createSapeur($data);

        //add new sapeur mutation
        $this->addMutation($sapeur->id, array(
            "localite_id" => $sapeur->localite_id,
            "incorporation" => $data['incorporation'],
            "motif" => ""
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
            $result = array_filter(
                $this->repository->getSapeurGradesById($sapeurId),
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
            $this->updateFonction(
                $sapeurId,
                array(
                    'id' => $data['fonction_sapeur_id'],
                    'fin' => $data['date_fonction'],
                    'remarque' => ''
                )
            );
        }

        //Add Fonction
        if ($data['fonction_id'] !== null) {
            $this->addFonction(
                $sapeurId,
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
        $res = array_filter(
            $this->repository->getSapeurGradesById($sapeurId),
            function ($grade) use ($gradeId) {
                return $grade->grade_id === $gradeId;
            }
        );

        if (count($res) !== 0) {
            throw new ArrayException(array('id' => "Grade déjà existant"));
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
        $fonctions = array_filter(
            $this->repository->getSapeurFonctionsById($sapeurId),
            function ($fonction) use ($fonctionId) {
                return $fonction->fonction_id === $fonctionId;
            }
        );

        $startDate = array_key_exists('debut', $data) ? date($data['debut']) : null;
        $endDate = array_key_exists('fin', $data) ? date($data['fin']) : null;

        //Check overlaps of a fonction
        foreach ($fonctions as $fonction) {
            $start = $fonction->debut;
            $end = $fonction->fin;

            if ($this->checkOverlappingPeriod($start, $end, $startDate, $endDate)) {
                throw new ArrayException([
                    "debut" => "Duplicated period",
                    "fin" => "Duplicated period",
                    "message" => "Fonction dupliquée durant une même période"
                ]);
            }
        }

        $fonction = $this->repository->addFonction($sapeurId, $data);
        $this->updateFonctionPrincipale($sapeurId);

        return $fonction;
    }

    public function updateFonction(int $sapeurId, $data)
    {
        $id = $data['id'];

        //Check si déjà présent
        $fonctions = $this->repository->getSapeurFonctionsById($sapeurId);

        //Get fonction to update
        $fonction = array_values(array_filter(
            $fonctions,
            function ($f) use ($id) {
                return $f->id === $id;
            }
        ))[0];
        $fonctionId = $fonction->fonction_id;

        $fonctions = array_filter(
            $fonctions,
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
                throw new ArrayException([
                    'debut' => "Duplicated period",
                    'fin' => 'Duplicated period',
                ]);
            }
        }

        //Update fonction
        $fonction = $this->repository->updateFonction($sapeurId, $data);
        $this->updateFonctionPrincipale($sapeurId);
        return $fonction;
    }

    public function removeFonction(int $sapeurId, int $fonctionSapeurId)
    {
        $this->repository->removeFonction($sapeurId, $fonctionSapeurId);
        $this->updateFonctionPrincipale($sapeurId);
    }

    public function finFonctions($sapeurId, $date, $fonctionsId)
    {
        $fonctions = $this->repository->getSapeurFonctionsById($sapeurId);

        //Contrôle que la date de fin ne soit pas antérieur à la date de début
        $dateFin = Carbon::parse($date);
        foreach ($fonctionsId as $id) {
            $fs = array_filter($fonctions, function ($f) use ($id) {
                return $f->id === $id;
            });
            if (count($fs) !== 1 || Carbon::parse($fs[0]->debut)->gte($dateFin)) {
                throw new ArrayException([
                    'fin' => 'Date de fin invalide',
                ]);
            }
        }

        foreach ($fonctionsId as $id) {
            $fs = array_filter($fonctions, function ($f) use ($id) {
                return $f->id === $id;
            });
            if (count($fs) === 1) {
                $f = $fs[0];
                $f->fin = $date;
                $this->repository->updateFonction($sapeurId, json_decode(json_encode($f), true));
            }
        }

        //$fonctions
        $this->updateFonctionPrincipale($sapeurId);

        return $this->repository->getSapeurFonctionsById($sapeurId);
    }

    private function verifyMutationPeriode($editedMutation, $mutations)
    {
        //Contrôle qu'une seule mutation peut ne pas avoir de date de fin
        if (!array_key_exists('sortie', $editedMutation) || is_null($editedMutation['sortie'])) {
            foreach ($mutations as $m) {
                if (is_null($m->sortie)) {
                    throw new ArrayException([
                        "sortie" => "Une seule mutation active à la fois",
                    ]);
                }
            }
        }

        //Contrôle que deux mutations ne se chevauchent pas
        $incorporation = Carbon::parse($editedMutation['incorporation']);
        $sortie = (!array_key_exists('sortie', $editedMutation) || is_null($editedMutation['sortie'])) ? Null : Carbon::parse($editedMutation['sortie']);
        foreach ($mutations as $m) {
            //Check overlapping periodes
            $incorporationTemp = Carbon::parse($m->incorporation);
            $sortieTemp = is_null($m->sortie) ? null : Carbon::parse($m->sortie);
            if (
                is_null($sortieTemp) && $sortie->gte($incorporationTemp) ||
                is_null($sortie) && $incorporation->lte($sortieTemp) ||
                !is_null($sortieTemp) && !is_null($sortie) && ($incorporation->gte($incorporationTemp) && $incorporation->lte($sortieTemp) ||
                    $sortie->gte($incorporationTemp) && $sortie->lte($sortieTemp))
            ) {
                throw new ArrayException([
                    "sortie" => "Deux mutations en conflits",
                    "incorporation" => "Deux mutations en conflits",
                ]);
            }
        }
    }

    private function isStillActif($mutations) {
        foreach($mutations as $mutation) {
            if ($mutation->sortie === NULL) {
                return true;
            }
        }
        return false;
    }

    public function addMutation($sapeurId, $data)
    {
        $mutations = $this->repository->getSapeurMutationsById($sapeurId);
        $this->verifyMutationPeriode($data, $mutations);

        $mutation = $this->repository->addMutation($sapeurId, $data);

        // Update actif statut depending of end of all mutation
        array_push($mutations, $mutation);
        $actif = $this->isStillActif($mutations) ? 1 : 0;
        $this->repository->updateSapeurStatusById($sapeurId, $actif);
        return ["mutation" => $mutation, "actif" => $actif];
    }

    public function updateMutation(int $sapeurId, $data)
    {
        //Update mutation
        $mutationId = $data['id'];
        $mutations = array_filter($this->repository->getSapeurMutationsById($sapeurId), function ($m) use ($mutationId) {
            return $m->id !== $mutationId;
        });
        $this->verifyMutationPeriode($data, $mutations);

        $mutation = $this->repository->updateMutation($sapeurId, $data);
        
        // Update actif statut depending of end of all mutation
        array_push($mutations, $mutation);
        $actif = $this->isStillActif($mutations) ? 1 : 0;
        $this->repository->updateSapeurStatusById($sapeurId, $actif);
        return ["mutation" => $mutation, "actif" => $actif];
    }

    /**
     * Supppression d'une mutation
     *
     * @param int $mutationId
     */
    public function removeMutation(int $sapeurId, int $mutationId)
    {
        // Check at least one mutation
        // Attention, quand on ajoutera les politiques, il faudra enlever cette limitation pour ce type de personnes
        if (count($this->repository->getSapeurMutationsById($sapeurId)) === 0) {
            throw new ArrayException([
                "info" => "Au moins une mutation nécessaire",
            ]);
        }
        $this->repository->removeMutation($sapeurId, $mutationId);

        // Update actif statut depending of end of all mutation
        $mutations = $this->repository->getSapeurMutationsById($sapeurId);
        $actif = $this->isStillActif($mutations) ? 1 : 0;
        $this->repository->updateSapeurStatusById($sapeurId, $actif);
        return $actif;
    }

    public function addTelephone(int $sapeurId, $data)
    {
        $telephones = $this->repository->getSapeurTelephonesById($sapeurId);
        foreach ($telephones as $tel) {
            if (strcmp(
                trim(preg_replace('/\s+/', ' ', $tel->numero)),
                trim(preg_replace('/\s+/', ' ', $data['numero']))
            ) === 0) {
                throw new ArrayException(['numero' => 'Duplicated numero']);
            }
        }

        return $this->repository->addTelephone($sapeurId, $data);
    }

    public function updateTelephone(int $sapeurId, $data)
    {
        $telephones = $this->repository->getSapeurTelephonesById($sapeurId);

        $telephoneId = $data['id'];

        $telephone = array_filter(
            $telephones,
            function ($t) use ($telephoneId) {
                return $t->id === $telephoneId;
            }
        );
        $telephones = array_filter(
            $telephones,
            function ($t) use ($telephoneId) {
                return $t->id !== $telephoneId;
            }
        );

        foreach ($telephones as $tel) {
            if (strcmp(
                trim(preg_replace('/\s+/', ' ', $tel->numero)),
                trim(preg_replace('/\s+/', ' ', $data['numero']))
            ) === 0) {
                throw new ArrayException(['numero' => 'Duplicated numero']);
            }
        }

        return $this->repository->updateTelephone($sapeurId, $data);
    }

    public function removeTelephone(int $sapeurId, int $telephoneId)
    {
        $this->repository->removeTelephone($sapeurId, $telephoneId);
    }

    public function addPermis(int $sapeurId, $data)
    {
        $permisId = $data['permis_type_id'];
        $res = array_filter(
            $this->repository->getSapeurPermisById($sapeurId),
            function ($p) use ($permisId) {
                return $p->permis_type_id === $permisId;
            }
        );

        //Check si sapeur as déjà ce permis
        if (count($res) !== 0) {
            throw new ArrayException(array('id' => "Unable to find permis"));
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

    public function removeGroupes($sapeurId, $groupesIds)
    {
        $this->repository->removeGroupes($sapeurId, $groupesIds);
        return $this->repository->getSapeurGroupesbyId($sapeurId);
    }

    /* ************************************************** *
     *                  METHODES PRIVEES                  *
     * ************************************************** */

    private function checkOverlappingPeriod($start1, $end1, $start2, $end2)
    {
        return ($end1 === null && $end2 === null ||
            $end1 === null && $start1 <= $end2 ||
            $end2 === null && $end1 >= $start2 ||
            $end1 !== null && $end2 !== null && !($end1 < $start2 || $end2 < $start1));
    }

    private function updateFonctionPrincipale($sapeurId)
    {
        $maxTri = -1;
        $maxId = -1;

        //FIXME Recupérer avec fonctions pour le tri
        $fonctions = array_filter(
            $this->repository->getSapeurFonctionsById($sapeurId, true),
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
