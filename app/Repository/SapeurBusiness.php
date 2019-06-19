<?php


namespace App\Repository;


use App\Exceptions\ArrayValidatorException;
use App\Models\CoursSapeur;
use App\Models\FonctionSapeur;
use App\Models\GradeSapeur;
use App\Models\Mutation;
use App\Models\Permis;
use App\Models\Sapeur;
use App\Models\SapeurTelephone;
use Validator;

class SapeurBusiness
{

    protected $sapeur;

    public function __construct(Sapeur $sapeur)
    {
        $this->sapeur = $sapeur;
    }

    /**
     * Get's a sapeur by it's ID
     *
     * @param int
     * @return SapeurBusiness
     */
    public static function get($sapeur_id)
    {
        return new SapeurBusiness(Sapeur::findOrFail($sapeur_id));
    }

    /**
     * Return sapeur data
     *
     * @return Sapeur
     */
    public function getData()
    {
        return $this->sapeur;
    }

    /**
     * Create a sapeur
     *
     * @param $data
     * @return SapeurBusiness
     * @throws ArrayValidatorException
     */
    public static function createSapeur($data)
    {
        if (array_key_exists('suffixe', $data) && $data['suffixe'] === null) {
            $data['suffixe'] = '';
        }

        $sapeur = new Sapeur();
        $sapeur->fill($data);
        $sapeur->save();

        return new SapeurBusiness($sapeur);
    }

    /**
     * Delete a sapeur.
     *
     * @param int
     */
    public static function delete($sapeur_id)
    {
        //TODO: Check
        Sapeur::destroy($sapeur_id);
    }

    /**
     * Updates a post.
     *
     * @param int
     * @param array
     * @return Sapeur
     * @throws ArrayValidatorException
     */
    public function update($data)
    {
        if (array_key_exists('suffixe', $data) && $data['suffixe'] === null) {
            $data['suffixe'] = '';
        }

        $this->sapeur->update($data);

        return $this->sapeur;
    }

    public function addCours($data)
    {
        //Add Cours
        $cours = new CoursSapeur();
        $cours->fill($data);
        $cours->cours_id = $data['cours_id'];
        $cours->localite_id = $data['localite_id'];
        $this->sapeur->cours()->save($cours);

        //Add Grade
        if ($data['grade_id'] !== null) {
            //Add grade if not already there
            $grade = $this->sapeur->grades()->where('grade_id', $data['grade_id'])->first();
            if ($grade === null) {
                $this->addGrade(array(
                    'grade_id' => $data['grade_id'],
                    'date' => $data['date_grade'],
                    'remarque' => ''
                ));
            }
        }

        //Edit old fonction
        if ($data['fonction_sapeur_id'] !== null) {
            $this->updateFonction(array(
                'id' => $data['fonction_sapeur_id'],
                'fin' => $data['date_fonction'],
                'remarque' => ''
            ));
        }

        //Add Fonction
        if ($data['fonction_id'] !== null) {
//            $fonction = $this->sapeur->fonctions()->where('fonction_id', $data['fonction_id'])->first();
//            if ($fonction === null) {
            $this->addFonction(array(
                'fonction_id' => $data['fonction_id'],
                'debut' => $data['date_fonction'],
                'fin' => null,
                'remarque' => null
            ));
//            }
        }

        return $cours;
    }

    public function updateCours($data)
    {
        //Update cours
        $cours = $this->sapeur->cours()->where('cours_sapeur.id', $data['id'])->first();

        //Search for the cours
        if ($cours === null) {
            throw new ArrayValidatorException(array('id' => "Unable to find cours"));
        } else {
            //Update mutation
            $cours->update($data);
            $cours->save();
        }

        return $cours;
    }

    /**
     * Remove a cours
     *
     * @param int $cours_sapeur_id
     */
    public function removeCours(int $cours_sapeur_id)
    {
        $this->sapeur->cours()->where('cours_sapeur.id', $cours_sapeur_id)->delete();
    }

    /**
     * Ajout d'un grade
     *
     * @param $data
     * @return GradeSapeur
     * @throws ArrayValidatorException
     */
    public function addGrade($data)
    {
        if (array_key_exists('remarque', $data) && $data['remarque'] === null) {
            $data['remarque'] = '';
        }

        //Check si déjà présent
        $grade = $this->sapeur->grades()->where('grade_id', $data['grade_id'])->first();

        if ($grade !== null) {
            throw new ArrayValidatorException(array('id' => "Grade déjà existant"));
        }

        //Creation du grade
        $grade = new GradeSapeur();
        $grade->fill($data);
        $grade->grade_id = $data['grade_id'];

        //Ajout du grade au sapeur
        $this->sapeur->grades()->save($grade);

        $this->updateMainGrade();

        return $grade;
    }

    /**
     * Modifie un grade
     *
     * @param $data
     * @return GradeSapeur
     * @throws ArrayValidatorException
     */
    public function updateGrade($data)
    {
        if (array_key_exists('remarque', $data) && $data['remarque'] === null) {
            $data['remarque'] = '';
        }

        //Update grade
        $grade = $this->sapeur->grades()->where('grade_sapeur.id', $data['id'])->first();

        //Search for the grade
        if ($grade === null) {
            throw new ArrayValidatorException(array('id' => "Unable to find grade"));
        } else {
            //Update mutation
            $grade->update($data);
            $grade->save();
        }

        $this->updateMainGrade();

        return $grade;
    }

    /**
     * Supppression d'un grade
     *
     * @param int $sapeur_grade_id
     */
    public function removeGrade(int $grade_sapeur_id)
    {
        $this->sapeur->grades()->where('grade_sapeur.id', $grade_sapeur_id)->delete();

        $this->updateMainGrade();
    }

    /**
     * Ajout d'une mutation
     *
     * @param $data
     * @return FonctionSapeur
     * @throws ArrayValidatorException
     */
    public function addFonction($data)
    {
        if (array_key_exists('remarque', $data) && $data['remarque'] === null) {
            $data['remarque'] = '';
        }

        $fonctions = $this->sapeur->fonctions()->where('fonction_id', $data['fonction_id'])->get();

        $startDate = $data['debut'] !== null ? date($data['debut']) : null;
        $endDate = $data['fin'] !== null ? date($data['fin']) : null;

        //Check overlaps of a fonction
        foreach ($fonctions as $fonction) {
            $start = $fonction->debut;
            $end = $fonction->fin;

            if ($this->checkOverlappingPeriod($start, $end, $startDate, $endDate)) {
                throw new ArrayValidatorException([
                    'debut' => "Duplicated period",
                    'fin' => 'Duplicated period',
                ]);
            }
        }

        //Create mutation
        $fonction = new FonctionSapeur();
        $fonction->fill($data);
        $fonction->fonction_id = $data['fonction_id'];

        //Ajout de la mutation au sapeur
        $this->sapeur->fonctions()->save($fonction);

        $this->updateMainFonction();

        return $fonction;
    }

    /**
     * Modifie une mutation
     *
     * @param $data
     * @return FonctionSapeur
     * @throws ArrayValidatorException
     */
    public function updateFonction($data)
    {
        if (array_key_exists('remarque', $data) && $data['remarque'] === null) {
            $data['remarque'] = '';
        }

        $id = $data['id'];

        //Update fonction
        $fonction = $this->sapeur->fonctions()->where('fonction_sapeur.id', $id)->first();

        $fonctions = $this->sapeur->fonctions()
            ->where('fonction_id', $fonction->fonction_id)
            ->where('fonction_sapeur.id', '!=', $id)
            ->get();

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

        //Search for the fonction
        if ($fonction === null) {
            throw new ArrayValidatorException(array('id' => "Unable to find fonction"));
        } else {
            //Update fonction
            $fonction->update($data);
            $fonction->save();
        }

        $this->updateMainFonction();

        return $fonction;
    }

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

    /**
     * Supppression d'une mutation
     *
     * @param int $mutation_id
     */
    public function removeFonction(int $fonction_sapeur_id)
    {
        $this->sapeur->fonctions()->where('fonction_sapeur.id', $fonction_sapeur_id)->delete();

        $this->updateMainFonction();
    }

    /**
     * Mets à jour la fonction principale d'un sapeur
     */
    private function updateMainFonction()
    {
        $maxTri = -1;
        $maxId = -1;
        foreach ($this->sapeur->fonctions()->where('fin', null)->with('fonction')->get() as $fonctionSapeur) {
            if ($fonctionSapeur->fonction->tri > $maxTri) {
                $maxId = $fonctionSapeur->fonction->id;
                $maxTri = $fonctionSapeur->fonction->tri;
            }
        }
        $this->sapeur->fonction_id = $maxId <= 0 ? null : $maxId;
        $this->sapeur->save();
    }


    /**
     * Mets à jour la grade principale d'un sapeur
     */
    private function updateMainGrade()
    {
        $maxTri = -1;
        $maxId = -1;
        foreach ($this->sapeur->grades()->with('grade')->get() as $gradeSapeur) {
            if ($gradeSapeur->grade->tri > $maxTri) {
                $maxId = $gradeSapeur->grade->id;
                $maxTri = $gradeSapeur->grade->tri;
            }
        }
        $this->sapeur->grade_id = $maxId <= 0 ? null : $maxId;
        $this->sapeur->save();
    }

    /**
     * Ajout d'une mutation
     *
     * @param $data
     * @return Mutation
     * @throws ArrayValidatorException
     */
    public function addMutation($data)
    {
        // Ajout d'une nouvelle mutation
        $validation = Validator::make($data,
            array(
                'incorporation' => 'required|date',
                'sortie' => 'date|nullable|after:incorporation',
                'motif' => 'string|nullable',
                'localite_id' => 'required|integer|exists:localites,id',
            )
        );

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        if (array_key_exists('motif', $data) && $data['motif'] === null) {
            $data['motif'] = '';
        }

        //Create mutation
        $mutation = new Mutation();
        $mutation->fill($data);

        //Ajout de la mutation au sapeur
        $this->sapeur->mutations()->save($mutation);

        return $mutation;
    }

    /**
     * Modifie une mutation
     *
     * @param $data
     * @return Mutation
     * @throws ArrayValidatorException
     */
    public function updateMutation($data)
    {
        $validation = Validator::make($data,
            array(
                'id' => 'required|integer|exists:mutations,id',
                'incorporation' => 'date',
                'sortie' => 'date|nullable|after:incorporation',
                'motif' => 'string|nullable',
                'localite_id' => 'integer|exists:localites,id',
            )
        );

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        if (array_key_exists('motif', $data) && $data['motif'] === null) {
            $data['motif'] = '';
        }

        //Update mutation
        $mutation = $this->sapeur->mutations()->where('mutations.id', $data['id'])->first();

        //Search for the mutation
        if ($mutation === null) {
            throw new ArrayValidatorException(array('id' => "Unable to find mutation"));
        } else {
            //Update mutation
            $mutation->update($data);
            $mutation->save();
        }

        return $mutation;
    }

    /**
     * Supppression d'une mutation
     *
     * @param int $mutation_id
     */
    public function removeMutation(int $mutation_id)
    {
        $this->sapeur->mutations()->where('mutations.id', $mutation_id)->delete();
    }

    /**
     * Add a Telephone
     *
     * @param array $data
     * @return SapeurTelephone
     * @throws ArrayValidatorException
     */
    public function addTelephone($data)
    {
        $validation = Validator::make($data,
            array(
                'telephone_type_id' => 'required|integer|exists:telephone_types,id',
                'numero' => 'required|string|min:2',
                'priorite' => 'required|integer',
                'rta' => 'required|boolean',
            )
        );

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        //TODO: Check if this numero already exist

        //Create permis
        $telephone = new SapeurTelephone();
        $telephone->fill($data);

        //Ajout du permis au sapeur
        $this->sapeur->telephones()->save($telephone);

        return $telephone;
    }

    /**
     * Update a Telephone informations
     *
     * @param array $data
     * @return SapeurTelephone
     * @throws ArrayValidatorException
     */
    public function updateTelephone($data)
    {
        //TODO check duplicated number
        $validation = Validator::make($data,
            array(
                'id' => 'required|integer|exists:sapeur_telephone,id',
                'telephone_type_id' => 'integer|exists:telephone_types,id',
                'numero' => 'string|min:2',
                'priorite' => 'integer',
                'rta' => 'boolean',
            )
        );

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        $telephone = $this->sapeur->telephones()->where('sapeur_telephone.id', $data['id'])->first();

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

    /**
     * Remove a Telephone
     *
     * @param int $permis_id
     */
    public function removeTelephone(int $telephone_id)
    {
        $this->sapeur->telephones()->where('sapeur_telephone.id', $telephone_id)->delete();
    }

    /**
     * Add a permis
     *
     * @param array $data
     * @return Permis
     * @throws ArrayValidatorException
     */
    public function addPermis($data)
    {
        $validation = Validator::make($data,
            array(
                'permis_type_id' => 'required|integer|exists:permis_types,id',
                'date' => 'required|date|before:tomorrow',
            )
        );

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        $permis = $this->sapeur->permis()->where('permis_type_id', $data['permis_type_id'])->first();

        //Check si sapeur as déjà ce permis
        if ($permis !== null) {
            throw new ArrayValidatorException(array('id' => "Unable to find permis"));
        } else {
            //Create permis
            $permis = new Permis();
            $permis->fill($data);
            $permis->permis_type_id = $data['permis_type_id'];

            //Ajout du permis au sapeur
            $this->sapeur->permis()->save($permis);
        }
        return $permis;
    }

    /**
     * Update a permis informations
     *
     * @param array $data
     * @return Permis
     * @throws ArrayValidatorException
     */
    public function updatePermis($data)
    {
        $validation = Validator::make($data,
            array(
                'permis_id' => 'required|integer',
                'date' => 'required|date|before:tomorrow',
            )
        );

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        $permis = $this->sapeur->permis()->where('permis.id', $data['permis_id'])->first();

        //Check si sapeur as déjà ce permis
        if ($permis === null) {
            throw new ArrayValidatorException(array('id' => "Unknown permis"));
        } else {
            //Update permis
            $permis->update($data);
            $permis->save();
        }
        return $permis;
    }

    /**
     * Remove a Permis
     *
     * @param int $permis_id
     */
    public function removePermis(int $permis_id)
    {
        $this->sapeur->permis()->where('permis.id', $permis_id)->delete();
    }
}
