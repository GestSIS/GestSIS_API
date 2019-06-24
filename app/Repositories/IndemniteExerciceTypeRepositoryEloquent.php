<?php


namespace App\Repositories;

use App\Contracts\IndemniteExerciceTypeRepository;
use App\Models\IndemniteExerciceType;
use StdClass;

class IndemniteExerciceTypeRepositoryEloquent implements IndemniteExerciceTypeRepository
{
    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        return array_map($this->convertIndemnite, IndemniteExerciceType::with('fonctions')->all($columns)->toArray());
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        if (array_key_exists('lieu', $data) && $data['lieu'] === null) {
            $data['lieu'] = '';
        }

        if (array_key_exists('communications', $data) && $data['communications'] === null) {
            $data['communications'] = '';
        }

        $exercice = new IndemniteExerciceType();
        $exercice->fill($data);
        $exercice->save();
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id)
    {
        if (array_key_exists('lieu', $data) && $data['lieu'] === null) {
            $data['lieu'] = '';
        }

        if (array_key_exists('communications', $data) && $data['communications'] === null) {
            $data['communications'] = '';
        }

        $exercice = IndemniteExerciceType::find($id);
        $exercice->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        //TODO Delete fonctions
        return IndemniteExerciceType::where('id')->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        return $this->convertIndemnite(IndemniteExerciceType::with('fonctions')->find($id, $columns));
    }

    /**
     * @param $exercice
     * @return StdClass|null
     */
    protected function convertIndemnite($exercice)
    {
        if ($exercice == null) return null;

        $object = new StdClass();

        $object->id = $exercice->id;
        $object->designation = $exercice->designation;
        $object->solde = $exercice->solde;
        $object->indemnite = $exercice->indemnite;
        $object->solde_min = $exercice->solde_min;
        $object->solde_min_pour = $exercice->solde_min_pour;
        $object->type_unite_id = $exercice->type_unite_id;
        $object->compte_id = $exercice->compte_id;
        $object->par_fonction = $exercice->par_fonction;

        $indemnites = array();
        foreach ($exercice->fonctions() as $indemnite) {
            array_push($indemnites, $this->convertIndemniteFonction($indemnite));
        }
        $object->fonctions = $indemnites;

        return $object;
    }

    protected function convertIndemniteFonction($indemnite)
    {
        if ($indemnite == null) return null;

        $object = new StdClass();
        $object->id = $indemnite->id;
        $object->sapeur_id = $indemnite->sapeur_id;
        $object->exercice_id = $indemnite->exercice_id;
        $object->convoque = $indemnite->convoque;
        $object->present = $indemnite->present;
        $object->amende = $indemnite->amende;
        $object->excuse_type_id = $indemnite->excuse_type_id;

        return $object;
    }
}
