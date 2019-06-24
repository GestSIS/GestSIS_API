<?php


namespace App\Repositories;

use App\Contracts\IndemniteInterventionTypeRepository;
use App\Models\IndemniteInterventionType;
use StdClass;

class IndemniteInterventionTypeRepositoryEloquent implements IndemniteInterventionTypeRepository
{
    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        return array_map($this->convertIndemnite, IndemniteInterventionType::with('fonctions')->all($columns)->toArray());
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $intervention = new IndemniteInterventionType();
        $intervention->fill($data);
        $intervention->save();
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id)
    {
        $intervention = IndemniteInterventionType::find($id);
        $intervention->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        //TODO Delete fonctions
        return IndemniteInterventionType::where('id')->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        return $this->convertIndemnite(IndemniteInterventionType::with('fonctions')->find($id, $columns));
    }

    /**
     * @param $intervention
     * @return StdClass|null
     */
    protected function convertIndemnite($intervention)
    {
        if ($intervention == null) return null;

        $object = new StdClass();

        $object->id = $intervention->id;

        $object->designation = $intervention->designation;
        $object->solde = $intervention->solde;
        $object->solde_min = $intervention->solde_min;
        $object->solde_min_pour = $intervention->solde_min_pour;
        $object->taux_weekend = $intervention->taux_weekend;
        $object->taux_nuit = $intervention->taux_nuit;
        $object->debut = $intervention->debut;
        $object->fin = $intervention->fin;
        $object->compte_id = $intervention->compte_id;
        $object->phase_id = $intervention->phase_id;
        $object->type_unite_id = $intervention->type_unite_id;
        $object->par_fonction = $intervention->par_fonction;

        $indemnites = array();
        foreach ($intervention->fonctions() as $indemnite) {
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
        $object->indemnite_int_id = $indemnite->indemnite_int_id;

        return $object;
    }
}
