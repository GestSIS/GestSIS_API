<?php


namespace App\Repositories;

use App\Contracts\SapeurRepository;
use App\Models\CoursSapeur;
use App\Models\FonctionSapeur;
use App\Models\GradeSapeur;
use App\Models\Mutation;
use App\Models\Permis;
use App\Models\Sapeur;
use App\Models\SapeurTelephone;
use stdClass;

class SapeurRepositoryEloquent implements SapeurRepository
{
    private const SAPEUR_LIGHT_COLUMNS = ['id', 'nom', 'prenom', 'actif'];

    public function listeSapeurLight()
    {
        $temp = $this;
        return Sapeur::all(self::SAPEUR_LIGHT_COLUMNS)
            ->map(function ($sapeur) use ($temp) {
                return $temp->convertSapeurLight($sapeur);
            })->toArray();
    }

    public function getSapeurDetailsById(int $sapeurId, $with = [])
    {
        return $this->convertSapeur(Sapeur::with($with)->find($id), $with);
    }

    public function getSapeurGradesById(int $sapeurId)
    {
        $temp = $this;
        return GradeSapeur::where('sapeur_id', $sapeurId)
            ->map(function ($sapeur) use ($temp) {
                return $temp->convertGrade($sapeur);
            })->toArray();
    }

    public function getSapeurFonctionsById(int $sapeurId)
    {
        $temp = $this;
        return FonctionSapeur::where('sapeur_id', $sapeurId)
            ->map(function ($sapeur) use ($temp) {
                return $temp->convertFonction($sapeur);
            })->toArray();
    }

    public function getSapeurCoursById(int $sapeurId)
    {
        $temp = $this;
        return CoursSapeur::where('sapeur_id', $sapeurId)
            ->map(function ($sapeur) use ($temp) {
                return $temp->convertCours($sapeur);
            })->toArray();
    }

    public function getSapeurPermisById(int $sapeurId)
    {
        $temp = $this;
        return Permis::where('sapeur_id', $sapeurId)
            ->map(function ($sapeur) use ($temp) {
                return $temp->convertPermis($sapeur);
            })->toArray();
    }

    public function getSapeurMutationsById(int $sapeurId)
    {
        $temp = $this;
        return Mutation::where('sapeur_id', $sapeurId)
            ->map(function ($sapeur) use ($temp) {
                return $temp->convertMutation($sapeur);
            })->toArray();
    }

    public function createSapeur($data)
    {
        if (array_key_exists('suffixe', $data) && $data['suffixe'] === null) {
            $data['suffixe'] = '';
        }

        $sapeur = new Sapeur();
        $sapeur->fill($data);
        $sapeur->save();

        return $this->convertSapeur($sapeur);
    }

    public function updateSapeurById(int $sapeurId, $data)
    {
        if (array_key_exists('suffixe', $data) && $data['suffixe'] === null) {
            $data['suffixe'] = '';
        }

        CoursSapeur::where('sapeur_id', $sapeurId)->limit(1)->update($data);
    }

    public function deleteSapeurById($sapeurId)
    {
        //TODO FIXME !!!
        Sapeur::destroy($sapeurId);
    }

    public function addCours(int $sapeurId, $data)
    {
        //Add Cours
        $cours = new CoursSapeur();
        $cours->fill($data);
        $cours->cours_id = $data['cours_id'];
        $cours->localite_id = $data['localite_id'];
        $cours->sapeur_id = $sapeurId;
        $cours->save($cours);

        return $this->convertCours($cours);
    }

    public function updateCours(int $sapeurId, $data)
    {
        CoursSapeur::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
    }

    public function removeCours(int $sapeurId, int $coursId)
    {
        CoursSapeur::where('sapeur_id', $sapeurId)->where('id', $coursId)->limit(1)->delete();;
    }

    public function addGrade(int $sapeurId, $data)
    {
        if (array_key_exists('remarque', $data) && $data['remarque'] === null) {
            $data['remarque'] = '';
        }

        //Creation du grade
        $grade = new GradeSapeur();
        $grade->fill($data);
        $grade->grade_id = $data['grade_id'];
        $grade->sapeurId = $sapeurId;
        $grade->save();

        return $this->convertGrade($grade);
    }

    public function updateGrade(int $sapeurId, $data)
    {
        if (array_key_exists('remarque', $data) && $data['remarque'] === null) {
            $data['remarque'] = '';
        }
        if (array_key_exists('debut', $data) && $data['debut'] === "") {
            $data['debut'] = null;
        }
        if (array_key_exists('fin', $data) && $data['fin'] === "") {
            $data['fin'] = null;
        }

        GradeSapeur::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
    }

    public function removeGrade(int $sapeurId, int $gradeId)
    {
        GradeSapeur::where('sapeur_id', $sapeurId)->where('id', $gradeId)->limit(1)->delete();
    }

    public function addFonction(int $sapeurId, $data)
    {
        if (array_key_exists('remarque', $data) && $data['remarque'] === null) {
            $data['remarque'] = '';
        }
        if (array_key_exists('debut', $data) && $data['debut'] === "") {
            $data['debut'] = null;
        }
        if (array_key_exists('fin', $data) && $data['fin'] === "") {
            $data['fin'] = null;
        }

        $fonction = new FonctionSapeur();
        $fonction->fill($data);
        $fonction->fonction_id = $data['fonction_id'];
        $fonction->sapeur_id = $sapeurId;
        $fonction->save();

        return $this->convertFonction($fonction);
    }

    public function updateFonction(int $sapeurId, $data)
    {
        if (array_key_exists('remarque', $data) && $data['remarque'] === null) {
            $data['remarque'] = '';
        }

        FonctionSapeur::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
    }

    public function removeFonction(int $sapeurId, int $fonctionId)
    {
        FonctionSapeur::where('sapeur_id', $sapeurId)->where('id', $fonctionId)->limit(1)->delete();
    }

    public function addMutation(int $sapeurId, $data)
    {
        if (array_key_exists('motif', $data) && $data['motif'] === null) {
            $data['motif'] = '';
        }

        //Create mutation
        $mutation = new Mutation();
        $mutation->fill($data);
        $mutation->sapeur_id = $sapeurId;
        $mutation->save();

        return $this->convertMutation($mutation);
    }

    public function updateMutation(int $sapeurId, $data)
    {
        if (array_key_exists('motif', $data) && $data['motif'] === null) {
            $data['motif'] = '';
        }

        Mutation::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
    }

    public function removeMutation(int $sapeurId, int $mutationId)
    {
        FonctionSapeur::where('sapeur_id', $sapeurId)->where('id', $mutationId)->limit(1)->delete();
    }

    public function addTelephone(int $sapeurId, $data)
    {
        $telephone = new SapeurTelephone();
        $telephone->fill($data);
        $telephone->sapeur_id = $sapeurId;
        $telephone->save();

        return $this->convertTelephone($telephone);
    }

    public function updateTelephone(int $sapeurId, $data)
    {
        SapeurTelephone::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
    }

    public function removeTelephone(int $sapeurId, int $telephoneId)
    {
        SapeurTelephone::where('sapeur_id', $sapeurId)->where('id', $telephoneId)->limit(1)->delete();
    }

    public function addPermis(int $sapeurId, $data)
    {
        $permis = new Permis();
        $permis->fill($data);
        $permis->permis_type_id = $data['permis_type_id'];
        $permis->sapeurId = $sapeurId;
        $permis->save($permis);

        return $permis;
    }

    public function updatePermis(int $sapeurId, $data)
    {
        Permis::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
    }

    public function removePermis(int $sapeurId, int $permisId)
    {
        permis::where('sapeur_id', $sapeurId)->where('id', $permisId)->limit(1)->delete();
    }

    protected function convertSapeurLight($sapeur)
    {
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->actif = $sapeur->actif;

        return $object;
    }

    protected function convertSapeur($sapeur, $with = [])
    {
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->suffixe = $sapeur->suffixe;
        $object->rue = $sapeur->rue;
        $object->no_rue = $sapeur->no_rue;
        $object->date_naissance = $sapeur->date_naissance;
        $object->no_avs = $sapeur->no_avs;
        $object->profession = $sapeur->profession;
        $object->employeur = $sapeur->employeur;
        $object->lieu_de_travail = $sapeur->lieu_de_travail;
        $object->email = $sapeur->email;
        $object->actif = $sapeur->actif;
        $object->iban = $sapeur->iban;
        $object->iban_statut = $sapeur->iban_statut;
        $object->remarque = $sapeur->remarque;
        $object->porteur = $sapeur->porteur;
        $object->localite_id = $sapeur->localite_id;
        $object->civilite_id = $sapeur->civilite_id;
        $object->fonction_id = $sapeur->fonction_id;
        $object->grade_id = $sapeur->grade_id;

        if (in_array('mutations', $with)) {
            $mutations = array();
            foreach ($sapeur->mutations as $mutation) {
                array_push($mutations, $this->convertMutation($mutation));
            }
            $object->mutations = $mutations;
        }

        if (in_array('groupes', $with)) {
            $groupes = array();
            foreach ($sapeur->groupes as $groupe) {
                array_push($groupes, $this->convertGroupe($groupe));
            }
            $object->groupes = $groupes;
        }

        if (in_array('grades', $with)) {
            $grades = array();
            foreach ($sapeur->grades as $grade) {
                array_push($grades, $this->convertGrade($grade));
            }
            $object->grades = $grades;
        }

        if (in_array('permis', $with)) {
            $permis = array();
            foreach ($sapeur->permis as $p) {
                array_push($permis, $this->convertpermis($p));
            }
            $object->permis = $permis;
        }

        if (in_array('telephones', $with)) {
            $grades = array();
            foreach ($sapeur->telephones as $telephone) {
                array_push($telephones, $this->convertTelephone($telephone));
            }
            $object->telephones = $telephones;
        }

        if (in_array('fonctions', $with)) {
            $fonctions = array();
            foreach ($sapeur->fonctions as $fonction) {
                array_push($fonctions, $this->convertFonction($fonction));
            }
            $object->fonctions = $fonctions;
        }

        if (in_array('cours', $with)) {
            $cours = array();
            foreach ($sapeur->cours as $c) {
                array_push($cours, $this->convertcour($c));
            }
            $object->cours = $cours;
        }

        return $object;
    }

    protected function convertFonction($fonction)
    {
        if ($fonction == null) return null;

        $object = new StdClass();
        $object->id = $fonction->id;

        $object->fonction_id = $fonction->fonction_id;
        $object->sapeur_id = $fonction->sapeur_id;
        $object->debut = $fonction->debut;
        $object->fin = $fonction->fin;
        $object->remarque = $fonction->remarque;

        return $object;
    }

    protected function convertGrade($grade)
    {
        if ($grade == null) return null;

        $object = new StdClass();
        $object->id = $grade->id;

        $object->grade_id = $grade->grade_id;
        $object->sapeur_id = $grade->sapeur_id;
        $object->date = $grade->date;
        $object->remarque = $grade->remarque;

        return $object;
    }

    protected function convertCours($cours)
    {
        if ($cours == null) return null;

        $object = new StdClass();
        $object->id = $cours->id;

        $object->cours_id = $cours->cours_id;
        $object->sapeur_id = $cours->sapeur_id;
        $object->localite_id = $cours->localite_id;
        $object->date = $cours->date;

        return $object;
    }

    protected function convertTelephone($telephone)
    {
        if ($telephone == null) return null;

        $object = new StdClass();
        $object->id = $telephone->id;

        $object->sapeur_id = $telephone->sapeur_id;
        $object->telephone_type_id = $telephone->telephone_type_id;
        $object->numero = $telephone->numero;
        $object->priorite = $telephone->priorite;
        $object->rta = $telephone->rta;

        return $object;
    }

    protected function convertMutation($mutation)
    {
        if ($mutation == null) return null;

        $object = new StdClass();
        $object->id = $mutation->id;

        $object->localite_id = $mutation->localite_id;
        $object->sapeur_id = $mutation->sapeur_id;
        $object->incorporation = $mutation->incorporation;
        $object->sortie = $mutation->sortie;
        $object->motif = $mutation->motif;

        return $object;
    }

    protected function convertPermis($permis)
    {
        if ($permis == null) return null;

        $object = new StdClass();
        $object->id = $permis->id;

        $object->sapeur_id = $permis->sapeur_id;
        $object->permis_type_id = $permis->permis_type_id;
        $object->date = $permis->date;

        return $object;
    }

    protected function convertGroupe($groupe)
    {
        if ($groupe == null) return null;

        $object = new StdClass();
        $object->id = $groupe->id;

        $object->sapeur_id = $groupe->sapeur_id;
        $object->groupe_id = $groupe->groupe_id;

        return $object;
    }
}
