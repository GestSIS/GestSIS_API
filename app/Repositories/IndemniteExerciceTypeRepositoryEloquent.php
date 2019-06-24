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
        $indemnite = new IndemniteExerciceType();
        $indemnite->fill($data);
        $indemnite->save();
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id)
    {
        $indemnite = IndemniteExerciceType::find($id);
        $indemnite->update($data);
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
     * @param $indemnite
     * @return StdClass|null
     */
    protected function convertIndemnite($indemnite)
    {
        if ($indemnite == null) return null;

        $object = new StdClass();

        $object->id = $indemnite->id;
        $object->designation = $indemnite->designation;
        $object->solde = $indemnite->solde;
        $object->indemnite = $indemnite->indemnite;
        $object->solde_min = $indemnite->solde_min;
        $object->solde_min_pour = $indemnite->solde_min_pour;
        $object->type_unite_id = $indemnite->type_unite_id;
        $object->compte_id = $indemnite->compte_id;
        $object->par_fonction = $indemnite->par_fonction;

        $indemnites = array();
        foreach ($indemnite->fonctions() as $indemnite) {
            array_push($indemnites, $this->convertIndemniteFonction($indemnite));
        }
        $object->fonctions = $indemnites;

        return $object;
    }

    /**
     * @param $indemnite
     * @return StdClass|null
     */
    protected function convertIndemniteFonction($indemnite)
    {
        if ($indemnite == null) return null;

        $object = new StdClass();

        $object->id = $indemnite->id;
        $object->fonction_id = $indemnite->fonction_id;
        $object->solde = $indemnite->solde;
        $object->indemnite = $indemnite->indemnite;
        $object->indemnite_int_id = $indemnite->indemnite_int_id;

        return $object;
    }
}
