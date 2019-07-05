<?php


namespace App\Repositories;

use App\Contracts\EcritureRepository;
use App\Models\Ecriture;
use StdClass;


class EcritureRepositoryEloquent implements EcritureRepository
{

    /**
     * @param $exercice_id
     * @return mixed
     */
    public function listeEcritureForExercice($exercice_id)
    {
        $temp = $this;
        return Ecriture::where('exercice_id', $exercice_id)
            ->get()
            ->map(function ($ecriture) use ($temp) {
                return $temp->convertEcriture($ecriture);
            })->toArray();
    }

    /**
     * @param $intervention_id
     * @return mixed
     */
    public function listeEcritureForIntervention($intervention_id)
    {
        $temp = $this;
        return Ecriture::where('intervention_id', $intervention_id)
            ->get()
            ->map(function ($ecriture) use ($temp) {
                return $temp->convertEcriture($ecriture);
            })->toArray();
    }

    public function listeFraisAnnuelByExeComptableId($exerciceComptableId)
    {
        $temp = $this;
        return Ecriture::where('exercice_comptable_id', $exerciceComptableId)
            ->whereNotNull('frais_annuel_type_id')
            ->get()
            ->map(function ($ecriture) use ($temp) {
                return $temp->convertEcriture($ecriture);
            })->toArray();
    }

    public function listeIndemniteAnnuelByExeComptableId($exerciceComptableId)
    {
        $temp = $this;
        return Ecriture::where('exercice_comptable_id', $exerciceComptableId)
            ->whereNotNull('indemnite_annuel_type_id')
            ->get()
            ->map(function ($ecriture) use ($temp) {
                return $temp->convertEcriture($ecriture);
            })->toArray();
    }

    /**
     * @param $ecriture
     */
    public function persisteNewEcriture($ecriture)
    {
        if (!array_key_exists('solde_min', $ecriture)) $ecriture['solde_min'] = null;
        if (!array_key_exists('solde_min_pour', $ecriture)) $ecriture['solde_min_pour'] = null;
        if (!array_key_exists('taux', $ecriture)) $ecriture['taux'] = null;
        if (!array_key_exists('solde', $ecriture)) $ecriture['solde'] = 0;
        if (!array_key_exists('indemnite', $ecriture)) $ecriture['indemnite'] = 0;
        if (!array_key_exists('frais', $ecriture)) $ecriture['frais'] = 0;
        if (!array_key_exists('exercice_comptable_id', $ecriture)) $ecriture['exercice_comptable_id'] = null;
        if (!array_key_exists('intervention_id', $ecriture)) $ecriture['intervention_id'] = null;
        if (!array_key_exists('exercice_id', $ecriture)) $ecriture['exercice_id'] = null;
        if (!array_key_exists('indemnite_annuel_type_id', $ecriture)) $ecriture['indemnite_annuel_type_id'] = null;
        if (!array_key_exists('frais_annuel_type_id', $ecriture)) $ecriture['frais_annuel_type_id'] = null;

        $model = new Ecriture();
        $model->fill($ecriture);
        $model->save();
    }

//    /**
//     * @param array $data
//     * @param $id
//     * @return mixed
//     */
//    public function update(array $data, $id)
//    {
//        $ecriture = Ecriture::find($id);
//        $ecriture->update($data);
//    }
//
//    /**
//     * @param $id
//     * @return mixed
//     */
//    public function delete($id)
//    {
//        return Ecriture::where('id')->destroy($id);
//    }
//
//    /**
//     * @param $id
//     * @param array $columns
//     * @return mixed
//     */
//    public function find($id, $columns = array('*'))
//    {
//        return $this->convertEcriture(Ecriture::find($id, $columns));
//    }

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
