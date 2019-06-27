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


}
