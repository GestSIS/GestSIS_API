<?php


namespace App\Infrastructure\Repositories;

use App\Domaine\SPI\ExerciceRepository;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\HeureExercice;
use App\Infrastructure\Models\Sms;
use StdClass;

class ExerciceRepositoryEloquent implements ExerciceRepository
{
    private const EXERCICE_LIGHT_COLUMNS = [
        'id',
        'exercice_categorie_id',
        'designation',
        'date',
        'heure',
        'duree',
        'lieu',
        'communications',
        'designation',
        'statut',
        'localite_id',
        'exercice_comptable_id',
    ];

    public function listExerciceLight(int $exerciceComptableId)
    {
        $temp = $this;
        return Exercice
            ::where('exercice_comptable_id', $exerciceComptableId)
            ->withCount('sms')
            ->get(self::EXERCICE_LIGHT_COLUMNS)
            ->map(function ($exercice) use ($temp) {
                return $temp->convertExercice($exercice);
            })->toArray();
    }

    public function listeSapeurOfExerciceById(int $exerciceId)
    {
        $temp = $this;
        return ExerciceSapeur
            ::where('exercice_id', $exerciceId)
            ->get()
            ->map(function ($sapeur) use ($temp) {
                return $temp->convertSapeur($sapeur);
            })->toArray();
    }

    public function listExerciceOfSapeurById(int $exerciceComptableId, int $sapeurId)
    {
        $heures = HeureExercice::where('sapeur_id', '=', $sapeurId)->get()->toArray();
        $sapeurs = ExerciceSapeur::where('sapeur_id', '=', $sapeurId)->get()->toArray();

        $exercices = Exercice::where('exercice_comptable_id', '=', $exerciceComptableId)
            ->whereIn('id', array_merge(
                array_map(fn($h) => $h['exercice_id'], $heures),
                array_map(fn($h) => $h['exercice_id'], $sapeurs),
            ))->get()->toArray();

        $dictionary = [];
        foreach ($exercices as $exercice) {
            $dictionary[$exercice['id']] = $exercice;
            $dictionary[$exercice['id']]['heures'] = [];
            $dictionary[$exercice['id']]['presence'] = null;
        }

        foreach ($sapeurs as $sapeur) {
            if (array_key_exists($sapeur['exercice_id'], $dictionary)) {
                $dictionary[$sapeur['exercice_id']]['presence'] = $sapeur;
            }
        }
        foreach ($heures as $heure) {
            if (array_key_exists($heure['exercice_id'], $dictionary)) {
                $dictionary[$heure['exercice_id']]['heures'][] = $heure;
            }
        }
        return array_values($dictionary);
    }

    public function getExerciceStatutById(int $exerciceId)
    {
        return Exercice::findOrFail($exerciceId, 'statut')->statut;
    }

    public function deleteExerciceById($exerciceId)
    {
        ExerciceSapeur::where('exercice_id', '=', $exerciceId)->delete();
        HeureExercice::where('exercice_id', '=', $exerciceId)->delete();
        Sms::where('exercice_id', '=', $exerciceId)->delete();
        Exercice::where('id', '=', $exerciceId)->delete();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createExercice(array $data)
    {
        if (array_key_exists('lieu', $data) && $data['lieu'] === null) {
            $data['lieu'] = '';
        }

        if (array_key_exists('communications', $data) && $data['communications'] === null) {
            $data['communications'] = '';
        }

        $exercice = new Exercice();
        $exercice->fill($data);
        $exercice->exercice_categorie_id = $data['exercice_categorie_id'];
        $exercice->exercice_comptable_id = $data['exercice_comptable_id'];
        $exercice->save();

        return $this->convertExercice($exercice);
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function updateExerciceById(int $exerciceId, $data)
    {
        if (array_key_exists('lieu', $data) && $data['lieu'] === null) {
            $data['lieu'] = '';
        }

        if (array_key_exists('communications', $data) && $data['communications'] === null) {
            $data['communications'] = '';
        }

        $exercice = Exercice::find($exerciceId);
        $exercice->update($data);

        return $this->convertExercice($exercice);
    }

    public function getExerciceByIdWith(int $exerciceId, $with = [])
    {
        //TODO: validate $with
        $autorized = ['sapeurs', 'localite'];
        return $this->convertExercice(Exercice::with($with)->find($exerciceId), $with);
    }

    public function addSapeurToExercice(int $exerciceId, $data)
    {
        $sapeur = new ExerciceSapeur();
        $sapeur->fill($data);
        $sapeur->exercice_id = $exerciceId;
        $sapeur->sapeur_id = $data['sapeur_id'];

        $sapeur->save();
        return $sapeur->toArray();
    }

    public function editSapeurOfExercice(int $exerciceId, array $sapeur)
    {
        ExerciceSapeur
            ::where('exercice_id', $exerciceId)
            ->where('id', $sapeur['id'])
            ->update($sapeur);
    }

    public function removeSapeursFromExercice(int $exerciceId, array $ids)
    {
        ExerciceSapeur
            ::where('exercice_id', $exerciceId)
            ->whereIn('sapeur_id', $ids)
            ->delete();
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        $temp = $this;
        return Exercice::all($columns)->map(function ($exercice) use ($temp) {
            return $temp->convertExercice($exercice);
        })->toArray();
    }

    /**
     * @param int $exercice_id
     * @return mixed
     */
    public function getSapeurs(int $exercice_id)
    {
        $temp = $this;
        return Exercice::find($exercice_id)->sapeurs->map(function ($sapeur) use ($temp) {
            return $temp->convertSapeur($sapeur);
        })->toArray();
    }

    /**
     * @param int $exercice_id
     * @param $sapeur
     */
    public function addSapeur(int $exercice_id, $sapeur)
    {
        $sap = new ExerciceSapeur();
        $sap->fill($sapeur);
        $sap->exercice_id = $exercice_id;
        $sap->save();
    }

    public function supprimerConvocations(int $sapeurId, $exercicesIds)
    {
        ExerciceSapeur::where('sapeur_id', $sapeurId)->whereIn('exercice_id', $exercicesIds)->delete();
    }

    /**
     * @param $exercice
     * @return StdClass|null
     */
    protected function convertExercice($exercice, $with = [])
    {
        if ($exercice == null)
            return null;

        $object = new StdClass();
        $object->id = $exercice->id;
        $object->exercice_categorie_id = $exercice->exercice_categorie_id;
        $object->designation = $exercice->designation;
        $object->date = $exercice->date;
        $object->heure = $exercice->heure;
        $object->duree = $exercice->duree;
        $object->lieu = $exercice->lieu;
        $object->nbSms = $exercice->sms_count;
        $object->communications = $exercice->communications;
        $object->designation = $exercice->designation;
        $object->statut = $exercice->statut;
        $object->localite_id = $exercice->localite_id;
        $object->exercice_comptable_id = $exercice->exercice_comptable_id;

        if (in_array('sapeurs', $with)) {
            $temp = $this;
            $object->sapeurs = $exercice->sapeurs->map(function ($sap) use ($temp) {
                return $temp->convertSapeur($sap);
            })->toArray();
        }

        if (in_array('localite', $with)) {
            $object->localite = $this->convertLocalite($exercice->localite);
        }

        return $object;
    }

    //TODO Externalise this code else-where
    protected function convertLocalite($localite)
    {
        if ($localite == null)
            return null;

        $object = new StdClass();
        $object->id = intval($localite->id);

        $object->npa = $localite->npa;
        $object->designation = $localite->designation;

        return $object;
    }

    /**
     * @param $sapeur
     * @return StdClass|null
     */
    protected function convertSapeur($sapeur)
    {
        if ($sapeur == null)
            return null;

        $object = new StdClass();
        $object->id = intval($sapeur->id);
        $object->sapeur_id = intval($sapeur->sapeur_id);
        $object->exercice_id = intval($sapeur->exercice_id);
        $object->convoque = intval($sapeur->convoque);
        $object->present = intval($sapeur->present);
        $object->absent = intval($sapeur->absent);
        $object->remplace = intval($sapeur->remplace);
        $object->amende = boolval($sapeur->amende);
        $object->excuse_type_id = intval($sapeur->excuse_type_id);

        return $object;
    }

    /**
     * @param $sapeur
     * @return StdClass|null
     */
    protected function convertSapeurWithExercicesInfos($sapeur)
    {
        if ($sapeur == null)
            return null;

        $object = new StdClass();
        $object->id = intval($sapeur->id);
        $object->sapeur_id = intval($sapeur->sapeur_id);
        $object->exercice_id = intval($sapeur->exercice_id);
        $object->convoque = intval($sapeur->convoque);
        $object->present = intval($sapeur->present);
        $object->remplace = intval($sapeur->remplace);
        $object->amende = boolval($sapeur->amende);
        $object->statut = intval($sapeur->statut);
        $object->excuse_type_id = intval($sapeur->excuse_type_id);
        $object->date = $sapeur->date;
        $object->heure = $sapeur->heure;
        $object->localite_id = intval($sapeur->localite_id);
        $object->communications = $sapeur->communications;
        $object->designation = $sapeur->designation;
        $object->exercice_categorie_id = intval($sapeur->exercice_categorie_id);

        return $object;
    }
}
