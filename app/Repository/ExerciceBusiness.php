<?php


namespace App\Repository;


use App\Models\Exercice;
use Exception;

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
     * Create a exercice
     *
     * @param $data
     * @return ExerciceBusiness
     * @throws Exception
     */
    public static function createExercice($data)
    {
        $validation = Validator::make($data,
            array(
                'date' => 'date',
                'heure' => 'heure',
                'lieu' => 'string|nullable',
                'communication' => 'string',
                'designation' => 'string|nullable',
                'duree' => 'integer',
                'status' => 'integer',
                'exercice_categorie_id' => 'integer|exists:exercice_categories,id',
                'localite_id' => 'integer|exists:localites,id'
            ));

        if ($validation->fails()) {
            throw new Exception($validation->messages());
        }

        if ($data['lieu'] === null) {
            $data['lieu'] = '';
        }
        if ($data['designation'] === null) {
            $data['designation'] = '';
        }

        $exercice = new Exercice();
        $exercice->fill($data);
        $exercice->save();

        return new ExerciceBusiness($exercice);
    }

    /**
     * Delete a exercice.
     *
     * @param int
     */
    public static function delete($exercice_id)
    {
        //TODO: Check
        Exercice::destroy($exercice_id);
    }

    /**
     * Updates a post.
     *
     * @param int
     * @param array
     * @return Exercice
     * @throws Exception
     */
    public function update($data)
    {
        $validation = Validator::make($data,
            array(
                'date' => 'date',
                'heure' => 'heure',
                'lieu' => 'string|nullable',
                'communication' => 'string',
                'designation' => 'string|nullable',
                'duree' => 'integer',
                'status' => 'integer',
                'exercice_categorie_id' => 'integer|exists:exercice_categories,id',
                'localite_id' => 'integer|exists:localites,id'
            ));

        if ($validation->fails()) {
            throw new Exception($validation->messages());
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
     * Ajout de sapeurs d'un exercice
     *
     * @param $data
     */
    public function addSapeurs($data)
    {
        $sapeurs = $data['sapeurs'];

        foreach($sapeurs as $sapeur){
            //TODO
        }
    }

    /**
     * Modification de sapeurs d'un exercice
     *
     * @param $data
     */
    public function updateSapeurs($data)
    {
        $sapeurs = $data['sapeurs'];

        foreach($sapeurs as $sapeur){
            $sap = $this->exercice->sapeurs()->where('id', $sapeur['id'])->get();
            $sap->fill($sapeur);
            $sap->save();
        }
    }

    /**
     * Suppression de sapeurs d'un exercice
     *
     * @param $data
     */
    public function removeSapeurs($data)
    {
        $ids = $data['sapeurs'];

        $this->exercice->sapeurs()->whereIn('id', $ids)->delete();
    }
}
