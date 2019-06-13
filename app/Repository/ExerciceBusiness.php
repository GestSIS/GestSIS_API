<?php


namespace App\Repository;


use App\Exceptions\ArrayValidatorException;
use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use Illuminate\Database\Eloquent\Collection;
use Validator;

class ExerciceBusiness
{

    protected $exercice;

    public function __construct(Exercice $exercice)
    {
        $this->exercice = $exercice;
    }

    /**
     * Get's a exercice by it's ID
     *
     * @param int
     * @return ExerciceBusiness
     */
    public static function get($exercice_id)
    {
        return new ExerciceBusiness(Exercice::findOrFail($exercice_id));
    }

    /**
     * Return sapeur data
     *
     * @return Exercice
     */
    public function getData()
    {
        return $this->exercice;
    }

    /**
     * Create a exercice
     *
     * @param $data
     * @return ExerciceBusiness
     * @throws ArrayValidatorException
     */
    public static function createExercice($data)
    {
        //TODO Vérifier exercice comptable
        $validation = Validator::make($data,
            array(
                'date' => 'date',
                'heure' => 'date_format:H:i',
                'lieu' => 'string|nullable',
                'communication' => 'string',
                'designation' => 'string|nullable',
                'duree' => 'integer|min:1|max:780',
                'status' => 'integer',
                'exercice_categorie_id' => 'integer|exists:exercice_categories,id',
                'localite_id' => 'integer|exists:localites,id',
                'exercice_comptable_id' => 'integer|exists:exercice_comptables,id'
            ));

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        if ($data['lieu'] === null) {
            $data['lieu'] = '';
        }
        if ($data['designation'] === null) {
            $data['designation'] = '';
        }

        $exercice = new Exercice();
        $exercice->fill($data);
        $exercice->exercice_categorie_id = $data['exercice_categorie_id'];
        $exercice->exercice_comptable_id = $data['exercice_comptable_id'];
        $exercice->save();

        return new ExerciceBusiness($exercice);
    }

    /**
     * Updates a post.
     *
     * @param int
     * @param array
     * @return Exercice
     * @throws ArrayValidatorException
     */
    public function update($data)
    {
        $validation = Validator::make($data,
            array(
                'date' => 'date',
                'heure' => 'date_format:H:i',
                'lieu' => 'string|nullable',
                'communication' => 'string',
                'designation' => 'string|nullable',
                'duree' => 'integer|min:1|max:780',
                'status' => 'integer',
                'exercice_categorie_id' => 'integer|exists:exercice_categories,id',
                'localite_id' => 'integer|exists:localites,id'
            ));

        if ($validation->fails()) {
            throw new ArrayValidatorException($validation->errors());
        }

        if ($data['lieu'] === null) {
            $data['lieu'] = '';
        }
        if ($data['designation'] === null) {
            $data['designation'] = '';
        }

        $this->exercice->update($data);

        return $this->exercice;
    }

    /**
     * Delete a exercice.
     *
     * @param int
     */
    public static function delete($exercice_id)
    {
        ExerciceSapeur::where('exercice_id', $exercice_id)->delete();
        Exercice::destroy($exercice_id);
    }

    /**
     * Ajout de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException
     */
    public function addSapeurs($data)
    {
        $sapeurs = $data['sapeurs'];

        foreach ($sapeurs as $sapeur) {
            $sapeurId = $sapeur['sapeur_id'];
            $validation = Validator::make($sapeur,
                array(
                    'convoque' => 'required|boolean',
                    'present' => 'required|boolean',
                    'amende' => 'required|boolean',
                    'remplace' => 'required|boolean',
                    'excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
                    'sapeur_id' => 'required|integer|exists:sapeurs,id'
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            if ($this->exercice->sapeurs()->where('exercice_sapeur.sapeur_id', $sapeurId)->first() !== null) {
                throw new ArrayValidatorException(array('id' => "Duplicated sapeur"));
            }

            $sap = new ExerciceSapeur();
            $sap->fill($sapeur);
            $sap->sapeur_id = $sapeur['sapeur_id'];
            $this->exercice->sapeurs()->save($sap);
        }
        return $this->exercice->sapeurs()->get();
    }

    /**
     * Modification de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException
     */
    public function updateSapeurs($data)
    {
        $sapeurs = $data['sapeurs'];

        foreach ($sapeurs as $sapeur) {

            $sap = $this->exercice->sapeurs()->where('exercice_sapeur.id', $sapeur['id'])->first();
            $validation = Validator::make($sapeur,
                array(
                    'convoque' => 'required|boolean',
                    'present' => 'required|boolean',
                    'amende' => 'required|boolean',
                    'remplace' => 'required|boolean',
                    'excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
                ));

            if ($validation->fails()) {
                throw new ArrayValidatorException($validation->errors());
            }

            $sap->update($sapeur);
            $sap->save();
        }
        return $this->exercice->sapeurs()->get();
    }

    /**
     * Suppression de sapeurs d'un exercice
     *
     * @param $data
     */
    public function removeSapeurs($data)
    {
        $ids = $data['sapeurs'];

        $this->exercice->sapeurs()->whereIn('exercice_sapeur.id', $ids)->delete();
    }
}
