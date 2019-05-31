<?php


namespace App\Repository;


use App\Models\CoursSapeur;
use App\Models\FonctionSapeur;
use App\Models\GradeSapeur;
use App\Models\Mutation;
use App\Models\Permis;
use App\Models\Sapeur;
use App\Models\SapeurTelephone;
use Exception;
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
     * @throws Exception
     */
    public static function createSapeur($data)
    {
        $validation = Validator::make($data,
            array(
                'nom' => 'string|min:2',
                'prenom' => 'string|min:2',
                'suffixe' => 'string|nullable',
                'rue' => 'string|min:3',
                'no_rue' => 'string',
                'date_naissance' => 'date',
                'no_avs' => 'string',
                'profession' => 'string|max:80',
                'employeur' => 'string|max:150',
                'lieu_de_travail' => 'string|max:100',
                'email' => 'email',
                'actif' => 'integer',
                'iban' => 'string|max:100',
                'iban_status' => 'integer',
                'remarque' => 'string|max:300',
                'porteur' => 'boolean',
                'localite_id' => 'integer|exists:localites,id'
            ));

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        if ($data['suffixe'] === null) {
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
     * @throws Exception
     */
    public function update($data)
    {
        $validation = Validator::make($data,
            array(
                'nom' => 'string|min:2',
                'prenom' => 'string|min:2',
                'suffixe' => 'string|nullable',
                'rue' => 'string|min:3',
                'no_rue' => 'string',
                'date_naissance' => 'date',
                'no_avs' => 'string',
                'profession' => 'string|max:80',
                'employeur' => 'string|max:150',
                'lieu_de_travail' => 'string|max:100',
                'email' => 'email',
                'actif' => 'integer',
                'iban' => 'string|max:100',
                'iban_status' => 'integer',
                'remarque' => 'string|max:300',
                'porteur' => 'boolean',
                'localite_id' => 'integer|exists:localites,id'
            ));

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        if ($data['suffixe'] === null) {
            $data['suffixe'] = '';
        }

        $this->sapeur->update($data);

        return $this->sapeur;
    }

    public function addCours($data)
    {
        $validation = Validator::make($data,
            array(
                'date' => 'required|date|before:tomorrow',
                'localite_id' => 'integer|exists:localites,id',
                'cours_id' => 'required|integer|exists:cours,id',
                'fonction_sapeur_id' => 'integer|nullable',
                'fonction_id' => 'integer|nullable',
                'grade_id' => 'integer|nullable',
            )
        );

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

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
                    'date' => $data['date'],
                    'remarque' => ''
                ));
            }
        }

        //Edit old fonction
        if ($data['fonction_sapeur_id'] !== null) {
            $this->updateFonction(array(
                'id' => $data['fonction_sapeur_id'],
                'fin' => $data['date'],
                'remarque' => ''
            ));
        }

        //Add Fonction
        if ($data['fonction_id'] !== null) {
//            $fonction = $this->sapeur->fonctions()->where('fonction_id', $data['fonction_id'])->first();
//            if ($fonction === null) {
            $this->addFonction(array(
                'fonction_id' => $data['fonction_id'],
                'debut' => $data['date'],
                'fin' => null,
                'remarque' => null
            ));
//            }
        }

        return $cours;
    }

    public function updateCours($data)
    {
        $validation = Validator::make($data,
            array(
                'id' => 'integer|exists:cours_sapeur,id',
                'date' => 'date',
                'localite_id' => 'integer|exists:localites,id',
            )
        );

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        //Update cours
        $cours = $this->sapeur->cours()->where('cours_sapeur.id', $data['id'])->first();

        //Search for the cours
        if ($cours === null) {
            throw new Exception("Unable to find cours");
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
     * @throws Exception
     */
    public function addGrade($data)
    {
        // Ajout d'un nouveau grade
        $validation = Validator::make($data,
            array(
                'grade_id' => 'required|integer|exists:grades,id',
                'date' => 'required|date',
                'remarque' => 'string|nullable',
            )
        );

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        if ($data['remarque'] === null) {
            $data['remarque'] = '';
        }

        //Check si déjà présent
        $grade = $this->sapeur->grades()->where('grade_id', $data['grade_id'])->first();

        if ($grade !== null) {
            throw new Exception("Grade déjà existant");
        }

        //Creation du grade
        $grade = new GradeSapeur();
        $grade->fill($data);
        $grade->grade_id = $data['grade_id'];

        //Ajout du grade au sapeur
        $this->sapeur->grades()->save($grade);

        return $grade;
    }

    /**
     * Modifie un grade
     *
     * @param $data
     * @return GradeSapeur
     * @throws Exception
     */
    public function updateGrade($data)
    {
        $validation = Validator::make($data,
            array(
                'date' => 'date',
                'remarque' => 'string|nullable',
                'id' => 'required|integer|exists:grade_sapeur,id'
            )
        );

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        if ($data['remarque'] === null) {
            $data['remarque'] = '';
        }

        //Update grade
        $grade = $this->sapeur->grades()->where('grade_sapeur.id', $data['id'])->first();

        //Search for the mutation
        if ($grade === null) {
            throw new Exception("Unable to find grade");
        } else {
            //Update mutation
            $grade->update($data);
            $grade->save();
        }

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
    }

    /**
     * Ajout d'une mutation
     *
     * @param $data
     * @return FonctionSapeur
     * @throws Exception
     */
    public function addFonction($data)
    {
        $validation = Validator::make($data,
            array(
                'fonction_id' => 'required|integer|exists:fonctions,id',
                'debut' => 'required|date',
                'fin' => 'date|nullable|after_or_equal:debut',
                'remarque' => 'string|nullable',
            )
        );

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        if ($data['remarque'] === null) {
            $data['remarque'] = '';
        }

        //TODO Check duplicate fonction
//        $fonction = $this->sapeur->fonctions()->where('fonction_id', $data['fonction_id'])->first();
//        if ($fonction !== null) {
//            throw new Exception("Duplicated fonction");
//        }

        //Create mutation
        $fonction = new FonctionSapeur();
        $fonction->fill($data);
        $fonction->fonction_id = $data['fonction_id'];

        //Ajout de la mutation au sapeur
        $this->sapeur->fonctions()->save($fonction);

        return $fonction;
    }

    /**
     * Modifie une mutation
     *
     * @param $data
     * @return FonctionSapeur
     * @throws Exception
     */
    public function updateFonction($data)
    {
        $validation = Validator::make($data,
            array(
                'id' => 'required|integer|exists:fonction_sapeur,id',
                'debut' => 'date',
                'fin' => 'date|nullable|after:debut',
                'remarque' => 'string|nullable',
            )
        );

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        if ($data['remarque'] === null) {
            $data['remarque'] = '';
        }

        //Update fonction
        $fonction = $this->sapeur->fonctions()->where('fonction_sapeur.id', $data['id'])->first();

        //Search for the fonction
        if ($fonction === null) {
            throw new Exception("Unable to find fonction");
        } else {
            //Update fonction
            $fonction->update($data);
            $fonction->save();
        }

        return $fonction;
    }

    /**
     * Supppression d'une mutation
     *
     * @param int $mutation_id
     */
    public function removeFonction(int $fonction_sapeur_id)
    {
        $this->sapeur->fonctions()->where('fonction_sapeur.id', $fonction_sapeur_id)->delete();
    }

    /**
     * Ajout d'une mutation
     *
     * @param $data
     * @return Mutation
     * @throws Exception
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
            throw new Exception($validation->messages());
        }

        if ($data['motif'] === null) {
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
     * @throws Exception
     */
    public function updateMutation($data)
    {
        $validation = Validator::make($data,
            array(
                'incorporation' => 'date',
                'sortie' => 'date|nullable|after:incorporation',
                'motif' => 'string|nullable',
                'localite_id' => 'integer|exists:localites,id',
            )
        );

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        if ($data['motif'] === null) {
            $data['motif'] = '';
        }

        //Update mutation
        $mutation = $this->sapeur->mutations()->where('mutations.id', $data['mutation_id'])->first();

        //Search for the mutation
        if ($mutation === null) {
            throw new Exception("Unable to find mutation");
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
     * @throws Exception
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
            throw new Exception($validation->messages());
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
     * @throws Exception
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
            throw new Exception($validation->messages());
        }

        $telephone = $this->sapeur->telephones()->where('sapeur_telephone.id', $data['id'])->first();

        //Search for the telephone
        if ($telephone === null) {
            throw new Exception("Unable to find telephone");
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
     * @throws Exception
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
            throw new Exception($validation->messages());
        }

        $permis = $this->sapeur->permis()->where('permis_type_id', $data['permis_type_id'])->first();

        //Check si sapeur as déjà ce permis
        if ($permis !== null) {
            throw new Exception("Unable to find permis");
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
     * @throws Exception
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
            throw new Exception($validation->messages());
        }

        $permis = $this->sapeur->permis()->where('permis.id', $data['permis_id'])->first();

        //Check si sapeur as déjà ce permis
        if ($permis === null) {
            throw new Exception("Unknown permis");
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
