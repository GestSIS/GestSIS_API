<?php


namespace App\Infrastructure\Repositories;

use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\ControleMedical;
use App\Infrastructure\Models\CoursSapeur;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\FonctionSapeur;
use App\Infrastructure\Models\GradeSapeur;
use App\Infrastructure\Models\GroupeSapeur;
use App\Infrastructure\Models\HeureExercice;
use App\Infrastructure\Models\InterventionSapeur;
use App\Infrastructure\Models\Mutation;
use App\Infrastructure\Models\Permis;
use App\Infrastructure\Models\Sapeur;
use App\Infrastructure\Models\SapeurTelephone;
use App\Infrastructure\Models\Travail;
use Carbon\Carbon;
use DB;
use stdClass;

class SapeurRepositoryEloquent implements SapeurRepository
{
    private const SAPEUR_LIGHT_COLUMNS = ['id', 'nom', 'prenom', 'actif', 'email', 'localite_id', 'fonction_id', 'grade_id', 'civilite_id', 'date_naissance', 'type', 'annee_incorporation'];

    public function listeSapeurLight(bool $actif = false, bool $actifOuAvecMateriel = false)
    {
        $now = Carbon::now();
        $oneMonthFurther = Carbon::now()->addMonths(1);
        $query = Sapeur::with([
            'permis',
            'fonctions' => function ($query) use ($oneMonthFurther, $now) {
                $query->where('debut', '<=', $oneMonthFurther)->where(function ($query) use ($now) {
                    $query->where('fin', '=', null)
                        ->orWhere('fin', '>=', $now);
                });
            }
        ]);

        if ($actif) {
            $query = $query->where('actif', '=', 1);
        }
        if ($actifOuAvecMateriel) {
            $query = $query->where('actif', '=', 1)->orWhereHas('articles');
        }

        return $query->orderBy('nom_prenom')->get([...self::SAPEUR_LIGHT_COLUMNS, DB::raw("CONCAT(nom, ' ', prenom) AS nom_prenom")])->toArray();
    }

    public function getSapeurDetailsById(int $sapeurId, $with = [])
    {
        return $this->convertSapeur(Sapeur::with($with)->find($sapeurId), $with);
    }

    public function getSapeurGradesById(int $sapeurId, $withGrade = false)
    {
        $temp = $this;
        return GradeSapeur::where('sapeur_id', $sapeurId)
            ->with($withGrade ? ['grade'] : [])
            ->get()
            ->map(function ($grade) use ($temp, $withGrade) {
                return $temp->convertSapeurGrade($grade, $withGrade);
            })->toArray();
    }

    public function getSapeurFonctionsById(int $sapeurId, $withFonction = false)
    {
        $temp = $this;
        return FonctionSapeur::where('sapeur_id', $sapeurId)
            ->with($withFonction ? ['fonction'] : [])
            ->get()
            ->map(function ($fonction) use ($temp, $withFonction) {
                return $temp->convertSapeurFonction($fonction, $withFonction);
            })->toArray();
    }

    public function getSapeurCoursById(int $sapeurId)
    {
        $temp = $this;
        return CoursSapeur::where('sapeur_id', $sapeurId)
            ->get()
            ->map(function ($cours) use ($temp) {
                return $temp->convertSapeurCours($cours);
            })->toArray();
    }

    public function getSapeurPermisById(int $sapeurId)
    {
        $temp = $this;
        return Permis::where('sapeur_id', $sapeurId)
            ->get()
            ->map(function ($permis) use ($temp) {
                return $temp->convertSapeurPermis($permis);
            })->toArray();
    }

    public function getSapeurMutationsById(int $sapeurId)
    {
        $temp = $this;
        return Mutation::where('sapeur_id', $sapeurId)
            ->get()
            ->map(function ($mutation) use ($temp) {
                return $temp->convertSapeurMutation($mutation);
            })->toArray();
    }

    public function getSapeurTelephonesById(int $sapeurId)
    {
        $temp = $this;
        return SapeurTelephone::where('sapeur_id', $sapeurId)
            ->get()
            ->map(function ($telephone) use ($temp) {
                return $temp->convertSapeurTelephone($telephone);
            })->toArray();
    }

    public function getSapeurGroupesById(int $sapeurId)
    {
        $temp = $this;
        return GroupeSapeur::where('sapeur_id', $sapeurId)
            ->get()
            ->map(function ($groupe) use ($temp) {
                return $temp->convertSapeurGroupe($groupe);
            })->toArray();
    }

    public function createSapeur($data)
    {
        $nullableFields = ['suffixe', 'remarque', 'profession', 'employeur', 'lieu_de_travail', 'iban', 'email', 'no_avs'];
        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] === null) {
                $data[$field] = '';
            }
        }

        $sapeur = new Sapeur();
        $sapeur->fill($data);
        $sapeur['type'] = $data['type'];
        $sapeur->save();

        return $this->convertSapeur($sapeur);
    }

    public function updateSapeurById(int $sapeurId, $data)
    {
        $sapeur = Sapeur::find($sapeurId);
        if (!$sapeur) {
            return null;
        }

        $nullableFields = ['suffixe', 'remarque', 'profession', 'employeur', 'lieu_de_travail', 'iban', 'email', 'no_avs'];
        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] === null) {
                $data[$field] = '';
            }
        }

        Sapeur::where('id', $sapeurId)->limit(1)->update($data);
        return $this->convertSapeur(Sapeur::find($sapeurId));
    }

    public function updateSapeurStatusById(int $sapeurId, $actif, $anneeIncorporation)
    {
        Sapeur::where('id', $sapeurId)->limit(1)->update(array('actif' => $actif, 'annee_incorporation' => $anneeIncorporation));
    }

    public function deleteSapeurById($sapeurId)
    {
        CoursSapeur::where('sapeur_id', '=', $sapeurId)->delete();
        Permis::where('sapeur_id', '=', $sapeurId)->delete();
        GradeSapeur::where('sapeur_id', '=', $sapeurId)->delete();
        FonctionSapeur::where('sapeur_id', '=', $sapeurId)->delete();
        SapeurTelephone::where('sapeur_id', '=', $sapeurId)->delete();
        ExerciceSapeur::where('sapeur_id', '=', $sapeurId)->delete();
        HeureExercice::where('sapeur_id', '=', $sapeurId)->delete();
        InterventionSapeur::where('sapeur_id', '=', $sapeurId)->delete();
        GroupeSapeur::where('sapeur_id', '=', $sapeurId)->delete();
        ControleMedical::where('sapeur_id', '=', $sapeurId)->delete();
        Mutation::where('sapeur_id', '=', $sapeurId)->delete();
        Travail::where('sapeur_id', '=', $sapeurId)->delete();
        Sapeur::where('id', '=', $sapeurId)->limit(1)->delete();
    }

    public function addCours(int $sapeurId, $data)
    {
        //Add Cours
        $cours = new CoursSapeur();
        $cours->fill($data);
        $cours->cours_id = $data['cours_id'];
        $cours->localite_id = $data['localite_id'];
        $cours->sapeur_id = $sapeurId;
        $cours->save();

        return $this->convertSapeurCours($cours);
    }

    public function updateCours(int $sapeurId, $data)
    {
        CoursSapeur::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
        return $this->convertSapeurCours(CoursSapeur::find($data['id']));
    }

    public function removeCours(int $sapeurId, int $coursId)
    {
        CoursSapeur::where('sapeur_id', $sapeurId)->where('id', $coursId)->limit(1)->delete();
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
        $grade->sapeur_id = $sapeurId;
        $grade->save();

        return $this->convertSapeurGrade($grade);
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
        return $this->convertSapeurGrade(GradeSapeur::find($data['id']));
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

        return $this->convertSapeurFonction($fonction);
    }

    public function updateFonction(int $sapeurId, $data)
    {
        if (array_key_exists('remarque', $data) && $data['remarque'] === null) {
            $data['remarque'] = '';
        }

        FonctionSapeur::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
        return $this->convertSapeurFonction(FonctionSapeur::find($data['id']));
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

        return $this->convertSapeurMutation($mutation);
    }

    public function updateMutation(int $sapeurId, $data)
    {
        if (array_key_exists('motif', $data) && $data['motif'] === null) {
            $data['motif'] = '';
        }

        Mutation::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
        return $this->convertSapeurMutation(Mutation::find($data['id']));
    }

    public function removeMutation(int $sapeurId, int $mutationId)
    {
        Mutation::where('sapeur_id', $sapeurId)->where('id', $mutationId)->limit(1)->delete();
    }

    public function addTelephone(int $sapeurId, $data)
    {
        $telephone = new SapeurTelephone();
        $telephone->fill($data);
        $telephone->sapeur_id = $sapeurId;
        $telephone->save();

        return $this->convertSapeurTelephone($telephone);
    }

    public function updateTelephone(int $sapeurId, $data)
    {
        SapeurTelephone::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
        return $this->convertSapeurTelephone(SapeurTelephone::find($data['id']));
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
        $permis->sapeur_id = $sapeurId;
        $permis->save();

        return $permis;
    }

    public function updatePermis(int $sapeurId, $data)
    {
        Permis::where('sapeur_id', $sapeurId)->where('id', $data['id'])->limit(1)->update($data);
        return $this->convertSapeurPermis(Permis::find($data['id']));
    }

    public function removePermis(int $sapeurId, int $permisId)
    {
        permis::where('sapeur_id', $sapeurId)->where('id', $permisId)->limit(1)->delete();
    }

    public function removeGroupes(int $sapeurId, array $sapeurGroupeIds)
    {
        GroupeSapeur::where('sapeur_id', $sapeurId)->whereIn('id', $sapeurGroupeIds)->delete();
    }

    public function removeGroupe(int $sapeurId, int $sapeurGroupeId)
    {
        GroupeSapeur::where('sapeur_id', $sapeurId)->where('id', $sapeurGroupeId)->limit(1)->delete();
    }

    protected function convertSapeur($sapeur, $with = [])
    {
        if ($sapeur == null)
            return null;

        $object = new StdClass();
        $object->id = intval($sapeur->id);

        $object->nom = $sapeur->nom;
        $object->prenom = $sapeur->prenom;
        $object->nom_prenom = $sapeur->nom . " " . $sapeur->prenom;
        $object->suffixe = $sapeur->suffixe;
        $object->rue = $sapeur->rue;
        $object->no_rue = $sapeur->no_rue;
        $object->date_naissance = $sapeur->date_naissance;
        $object->annee_incorporation = $sapeur->annee_incorporation;
        $object->no_avs = $sapeur->no_avs;
        $object->cotisation_avs = $sapeur->cotisation_avs;
        $object->profession = $sapeur->profession;
        $object->employeur = $sapeur->employeur;
        $object->lieu_de_travail = $sapeur->lieu_de_travail;
        $object->email = $sapeur->email;
        $object->actif = $sapeur->actif;
        $object->iban = $sapeur->iban;
        $object->iban_statut = $sapeur->iban_statut;
        $object->remarque = $sapeur->remarque;
        $object->porteur = $sapeur->porteur;
        $object->localite_id = intval($sapeur->localite_id);
        $object->civilite_id = intval($sapeur->civilite_id);
        $object->fonction_id = intval($sapeur->fonction_id);
        $object->grade_id = intval($sapeur->grade_id);
        $object->type = intval($sapeur->type);

        if (in_array('mutations', $with)) {
            $mutations = array();
            foreach ($sapeur->mutations as $mutation) {
                array_push($mutations, $this->convertSapeurMutation($mutation));
            }
            $object->mutations = $mutations;
        }

        if (in_array('groupes', $with)) {
            $groupes = array();
            foreach ($sapeur->groupes as $groupe) {
                array_push($groupes, $this->convertSapeurGroupe($groupe));
            }
            $object->groupes = $groupes;
        }

        if (in_array('grades', $with)) {
            $grades = array();
            foreach ($sapeur->grades as $grade) {
                array_push($grades, $this->convertSapeurGrade($grade));
            }
            $object->grades = $grades;
        }

        if (in_array('permis', $with)) {
            $permis = array();
            foreach ($sapeur->permis as $p) {
                array_push($permis, $this->convertSapeurPermis($p));
            }
            $object->permis = $permis;
        }

        if (in_array('telephones', $with)) {
            $telephones = array();
            foreach ($sapeur->telephones as $telephone) {
                array_push($telephones, $this->convertSapeurTelephone($telephone));
            }
            $object->telephones = $telephones;
        }

        if (in_array('fonctions', $with)) {
            $fonctions = array();
            foreach ($sapeur->fonctions as $fonction) {
                array_push($fonctions, $this->convertSapeurFonction($fonction));
            }
            $object->fonctions = $fonctions;
        }

        if (in_array('cours', $with)) {
            $cours = array();
            foreach ($sapeur->cours as $c) {
                array_push($cours, $this->convertSapeurCours($c));
            }
            $object->cours = $cours;
        }

        return $object;
    }

    protected function convertSapeurFonction($fonction, $withFonction = false)
    {
        if ($fonction == null)
            return null;

        $object = new StdClass();
        $object->id = intval($fonction->id);

        $object->fonction_id = intval($fonction->fonction_id);
        $object->sapeur_id = intval($fonction->sapeur_id);
        $object->debut = $fonction->debut;
        $object->fin = $fonction->fin;
        $object->remarque = $fonction->remarque;

        if ($withFonction) {
            $object->fonction = $this->convertFonction($fonction->fonction);
        }

        return $object;
    }

    protected function convertFonction($fonction)
    {
        if ($fonction == null)
            return null;

        $object = new StdClass();
        $object->id = $fonction->id;

        $object->nom = $fonction->nom;
        $object->abreviation = $fonction->abreviation;
        $object->tri = $fonction->tri;
        $object->cumulable = $fonction->cumulable;

        return $object;
    }

    protected function convertSapeurGrade($grade, $withGrade = false)
    {
        if ($grade == null)
            return null;

        $object = new StdClass();
        $object->id = $grade->id;

        $object->grade_id = $grade->grade_id;
        $object->sapeur_id = $grade->sapeur_id;
        $object->date = $grade->date;
        $object->remarque = $grade->remarque;

        if ($withGrade) {
            $object->grade = $this->convertGrade($grade->grade);
        }

        return $object;
    }

    protected function convertGrade($grade)
    {
        if ($grade == null)
            return null;

        $object = new StdClass();
        $object->id = $grade->id;

        $object->designation = $grade->designation;
        $object->tri = $grade->tri;
        $object->abreviation = $grade->abreviation;
        $object->groupe = $grade->groupe;

        return $object;
    }

    protected function convertSapeurCours($cours)
    {
        if ($cours == null)
            return null;

        $object = new StdClass();
        $object->id = $cours->id;

        $object->cours_id = $cours->cours_id;
        $object->sapeur_id = $cours->sapeur_id;
        $object->localite_id = $cours->localite_id;
        $object->date = $cours->date;
        $object->duree = $cours->duree;

        return $object;
    }

    protected function convertSapeurTelephone($telephone)
    {
        if ($telephone == null)
            return null;

        $object = new StdClass();
        $object->id = $telephone->id;

        $object->sapeur_id = $telephone->sapeur_id;
        $object->telephone_type_id = $telephone->telephone_type_id;
        $object->numero = $telephone->numero;
        $object->priorite = $telephone->priorite;
        $object->rta = $telephone->rta;

        return $object;
    }

    protected function convertSapeurMutation($mutation)
    {
        if ($mutation == null)
            return null;

        $object = new StdClass();
        $object->id = $mutation->id;

        $object->localite_id = $mutation->localite_id;
        $object->sapeur_id = $mutation->sapeur_id;
        $object->incorporation = $mutation->incorporation;
        $object->sortie = $mutation->sortie;
        $object->motif = $mutation->motif;

        return $object;
    }

    protected function convertSapeurPermis($permis)
    {
        if ($permis == null)
            return null;

        $object = new StdClass();
        $object->id = $permis->id;

        $object->sapeur_id = $permis->sapeur_id;
        $object->permis_type_id = $permis->permis_type_id;
        $object->date = $permis->date;

        return $object;
    }

    protected function convertSapeurGroupe($groupe)
    {
        if ($groupe == null)
            return null;

        $object = new StdClass();
        $object->id = $groupe->id;

        $object->sapeur_id = $groupe->sapeur_id;
        $object->groupe_id = $groupe->groupe_id;

        return $object;
    }
}
