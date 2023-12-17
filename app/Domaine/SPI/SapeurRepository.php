<?php


namespace App\Domaine\SPI;


interface SapeurRepository
{
    public function listeSapeurLight();

    public function getSapeurDetailsById(int $sapeurId, $with = []);

    public function getSapeurGradesById(int $sapeurId, $withGrade = false);

    public function getSapeurFonctionsById(int $sapeurId, $withFonction = false);

    public function getSapeurCoursById(int $sapeurId);

    public function getSapeurPermisById(int $sapeurId);

    public function getSapeurMutationsById(int $sapeurId);

    public function getSapeurTelephonesById(int $sapeurId);

    public function getSapeurGroupesById(int $sapeurId);

    public function createSapeur($data);

    public function updateSapeurById(int $sapeurId, $data);
    public function updateSapeurStatusById(int $sapeurId, $actif, $anneeIncorporation);

    public function deleteSapeurById(int $sapeurId);

    public function addCours(int $sapeurId, $data);

    public function updateCours(int $sapeurId, $data);

    public function removeCours(int $sapeurId, int $coursId);

    public function addGrade(int $sapeurId, $data);

    public function updateGrade(int $sapeurId, $data);

    public function removeGrade(int $sapeurId, int $gradeId);

    public function addFonction(int $sapeurId, $data);

    public function updateFonction(int $sapeurId, $data);

    public function removeFonction(int $sapeurId, int $fonctionId);

    public function addMutation(int $sapeurId, $data);

    public function updateMutation(int $sapeurId, $data);

    public function removeMutation(int $sapeurId, int $mutationId);

    public function addTelephone(int $sapeurId, $data);

    public function updateTelephone(int $sapeurId, $data);

    public function removeTelephone(int $sapeurId, int $telephoneId);

    public function addPermis(int $sapeurId, $data);

    public function updatePermis(int $sapeurId, $data);

    public function removePermis(int $sapeurId, int $permisId);

    public function removeGroupes(int $sapeurId, array $groupeIds);

    public function removeGroupe(int $sapeurId, int $groupeId);
}
