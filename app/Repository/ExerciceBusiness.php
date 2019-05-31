<?php


namespace App\Repository;


use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use Exception;
use Illuminate\Database\Eloquent\Collection;

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
     * @return Collection
     * @throws Exception
     */
    public function addSapeurs($data)
    {
        $sapeurs = $data['sapeurs'];

        foreach ($sapeurs as $sapeur) {
            $validation = Validator::make($data,
                array(
                    'convoque' => 'required|boolean',
                    'present' => 'required|boolean',
                    'absent' => 'required|boolean',
                    'excuse' => 'required|boolean',
                    'sapeur_id' => 'required|integer|exists:sapeurs,id',
                ));

            if ($validation->fails()) {
                throw new Exception($validation->messages());
            }

            //TODO Validate data
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
     */
    public function updateSapeurs($data)
    {
        $sapeurs = $data['sapeurs'];

        foreach ($sapeurs as $sapeur) {
            //TODO Validate data
            $sap = $this->exercice->sapeurs()->where('exercice_sapeur.id', $sapeur['id'])->first();
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

        $this->exercice->sapeurs()->whereIn('id', $ids)->delete();
    }
}
