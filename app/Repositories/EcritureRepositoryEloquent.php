<?php


namespace App\Repositories;

use App\Contracts\EcritureRepository;
use App\Models\Ecriture;
use StdClass;


class EcritureRepositoryEloquent implements EcritureRepository
{
    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        $temp = $this;
        return Ecriture::all($columns)->map(function ($ecriture) use ($temp) {
            return $temp->convertEcriture($ecriture);
        });
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        if (!array_key_exists('solde_min', $data)) $data['solde_min'] = null;
        if (!array_key_exists('solde_min_pour', $data)) $data['solde_min_pour'] = null;
        if (!array_key_exists('taux', $data)) $data['taux'] = null;
        if (!array_key_exists('solde', $data)) $data['solde'] = 0;
        if (!array_key_exists('indemnite', $data)) $data['indemnite'] = 0;
        if (!array_key_exists('frais', $data)) $data['frais'] = 0;
        if (!array_key_exists('exercice_comptable_id', $data)) $data['exercice_comptable_id'] = null;
        if (!array_key_exists('intervention_id', $data)) $data['intervention_id'] = null;
        if (!array_key_exists('exercice_id', $data)) $data['exercice_id'] = null;
        if (!array_key_exists('indemnite_annuel_type_id', $data)) $data['indemnite_annuel_type_id'] = null;
        if (!array_key_exists('frais_annuel_type_id', $data)) $data['frais_annuel_type_id'] = null;

        $ecriture = new Ecriture();
        $ecriture->fill($data);
        $ecriture->save();
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id)
    {
        $ecriture = Ecriture::find($id);
        $ecriture->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return Ecriture::where('id')->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        return $this->convertEcriture(Ecriture::find($id, $columns));
    }

    /**
     * @param $ecriture
     * @return stdClass|null
     */
    protected function convertEcriture($ecriture)
    {
        if ($ecriture == null) return null;

        $object = new StdClass();
        $object->id = $ecriture->id;
        $object->designation = $ecriture->designation;
        $object->total = $ecriture->total;
        $object->tarif = $ecriture->tarif;
        $object->type_unite_id = $ecriture->type_unite_id;
        $object->quantite = $ecriture->quantite;
        $object->solde_min = $ecriture->solde_min;
        $object->solde_min_pour = $ecriture->solde_min_pour;
        $object->taux = $ecriture->taux;
        $object->solde = $ecriture->solde;
        $object->indemnite = $ecriture->indemnite;
        $object->frais = $ecriture->frais;
        $object->sapeur_id = $ecriture->sapeur_id;
        $object->exercice_comptable_id = $ecriture->exercice_comptable_id;
        $object->intervention_id = $ecriture->intervention_id;
        $object->exercice_id = $ecriture->exercice_id;
        $object->indemnite_annuel_type_id = $ecriture->indemnite_annuel_type_id;
        $object->frais_annuel_type_id = $ecriture->frais_annuel_type_id;

        return $object;
    }
}
