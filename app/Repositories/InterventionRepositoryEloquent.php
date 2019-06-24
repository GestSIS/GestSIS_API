<?php


namespace App\Repositories;

use App\Contracts\InterventionRepository;
use App\Models\Intervention;
use StdClass;

class InterventionRepositoryEloquent implements InterventionRepository
{
    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        $temp = $this;
        return Intervention::all($columns)->map(function ($intervention) use ($temp) {
            return $temp->convertIntervention($intervention);
        })->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        //TODO
        if (!array_key_exists('lieu', $data) || $data['lieu'] === null) $data['lieu'] = '';
        if (!array_key_exists('description', $data) || $data['description'] === null) $data['description'] = '';
        if (!array_key_exists('proprietaire', $data) || $data['proprietaire'] === null) $data['proprietaire'] = '';
        if (!array_key_exists('responsable', $data) || $data['responsable'] === null) $data['responsable'] = '';

        $intervention = new Intervention();
        $intervention->fill($data);
        $intervention->exercice_comptable_id = $data['exercice_comptable_id'];
        $intervention->save();
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id)
    {
        if (array_key_exists('lieu', $data) && $data['lieu'] === null) $data['lieu'] = '';
        if (array_key_exists('description', $data) && $data['description'] === null) $data['description'] = '';
        if (array_key_exists('proprietaire', $data) && $data['proprietaire'] === null) $data['proprietaire'] = '';
        if (array_key_exists('responsable', $data) && $data['responsable'] === null) $data['responsable'] = '';

        $intervention = Intervention::find($id);
        $intervention->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return Intervention::where('id')->destroy($id);
    }

    /**
     * @param $id
     * @param $with
     * @return StdClass|null
     */
    public function findWith($id, $with)
    {
        //TODO Check with allowed
        $allowedWith = ['presences', 'phases'];
        return $this->convertIntervention(Intervention::find($id, $with), $with);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        return $this->convertIntervention(Intervention::find($id, $columns));
    }

    /**
     * @param $intervention
     * @return StdClass|null
     */
    protected function convertIntervention($intervention, $with = [])
    {
        if ($intervention == null) return null;

        $object = new StdClass();
        $object->id = $intervention->id;

        $object->date_debut = $intervention->date_debut;
        $object->heure_debut = $intervention->heure_debut;
        $object->lieu = $intervention->lieu;
        $object->objet = $intervention->objet;
        $object->date_fin = $intervention->date_fin;
        $object->heure_fin = $intervention->heure_fin;
        $object->rapport_police = $intervention->rapport_police;
        $object->degre = $intervention->degre;
        $object->sauve_personne = $intervention->sauve_personne;
        $object->sauve_animaux = $intervention->sauve_animaux;
        $object->description = $intervention->description;
        $object->proprietaire = $intervention->proprietaire;
        $object->responsable = $intervention->responsable;
        $object->stat_nb = $intervention->stat_nb;
        $object->imputer = $intervention->imputer;
        $object->exercice_comptable_id = $intervention->exercice_comptable_id;
        $object->localite_id = $intervention->localite_id;
        $object->type_intervention_id = $intervention->type_intervention_id;
        $object->presence_id = $intervention->presence_id;
        $object->stat_federal_id = $intervention->stat_federal_id;
        $object->intervention_traitement_id = $intervention->intervention_traitement_id;

        if (in_array('presences', $with)) {
            $temp = $this;
            $object->presences = $intervention->presences->map(function ($sap) use ($temp) {
                return $temp->convertPresence($sap);
            })->toArray();
        }

        if (in_array('phases', $with)) {
            $temp = $this;
            $object->phases = $intervention->phases->map(function ($sap) use ($temp) {
                return $temp->convertPhase($sap);
            })->toArray();
        }

        return $object;
    }

    protected function convertPhase($phase)
    {
        if ($phase == null) return null;

        $object = new StdClass();
        $object->id = $phase->id;

        $object->debut = $phase->debut;
        $object->phase_type_id = $phase->phase_type_id;
        $object->intervention_id = $phase->intervention_id;

        return $object;
    }

    protected function convertPresence($presence)
    {
        if ($presence == null) return null;

        $object = new StdClass();
        $object->id = $presence->id;

        $object->debut = $presence->debut;
        $object->fin = $presence->fin;
        $object->piquet = $presence->piquet;
        $object->sapeur_id = $presence->sapeur_id;
        $object->intervention_id = $presence->intervention_id;

        return $object;
    }
}
