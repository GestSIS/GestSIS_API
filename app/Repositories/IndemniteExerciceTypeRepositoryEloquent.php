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

}
