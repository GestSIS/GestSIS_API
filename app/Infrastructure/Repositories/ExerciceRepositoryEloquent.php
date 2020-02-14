<?php


namespace App\Infrastructure\Repositories;

use App\Domaine\SPI\ExerciceRepository;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceSapeur;
use Illuminate\Support\Facades\DB;
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

    public function listExerciceLight()
    {
        $temp = $this;
        return Exercice
            ::all(self::EXERCICE_LIGHT_COLUMNS)
            ->map(function ($exercice) use ($temp) {
                return $temp->convertExercice($exercice);
            })->toArray();
    }

    public function listSapeurOfExerciceById($exerciceId)
    {
        $temp = $this;
        return ExerciceSapeur
            ::where('exercice_id', $exerciceId)
            ->get()
            ->map(function ($sapeur) use ($temp) {
                return $temp->convertSapeur($sapeur);
            })->toArray();
    }

    public function listExerciceOfSapeurById($exerciceComptableId, $sapeurId)
    {
        $temp = $this;
        return DB::table('exercice_sapeur')
            ->where('sapeur_id', $sapeurId)
            ->where('exercice_comptable_id', $exerciceComptableId)
            ->join('exercices', 'exercices.id', '=', 'exercice_sapeur.exercice_id')
            ->select('exercice_sapeur.*', 'exercices.date', 'exercices.heure', 'exercices.communications', 'exercices.localite_id', 'exercices.exercice_categorie_id')
            ->get()
            ->map(function ($sapeur) use ($temp) {
                return $temp->convertSapeurWithExercicesInfos($sapeur);
            })->toArray();
    }

    public function getExerciceStatutById($exerciceId)
    {
        return Exercice::findOrFail($exerciceId, 'statut')->statut;
    }

    public function deleteExerciceById($exerciceId)
    {
        Exercice::where('id', $exerciceId)->delete();
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
    public function updateExerciceById($exerciceId, $data)
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

    public function getExerciceByIdWith($exerciceId, $with = [])
    {
        //TODO validate $with
        $autorized = ['sapeurs', 'localite'];
        return $this->convertExercice(Exercice::with($with)->find($exerciceId), $with);
    }

    public function addSapeurToExercice($exerciceId, $data)
    {
        $sapeur = new ExerciceSapeur();
        $sapeur->fill($data);
        $sapeur->exercice_id = $exerciceId;
        $sapeur->sapeur_id = $data['sapeur_id'];

        $sapeur->save();
    }

    public function editSapeurOfExercice($exerciceId, $sapeurs)
    {
        ExerciceSapeur
            ::where('exercice_id', $exerciceId)
            ->where('id', $sapeurs['id'])
            ->update($sapeurs);
    }

    public function removeSapeursFromExercice($exerciceId, $ids)
    {
        ExerciceSapeur
            ::where('exercice_id', $exerciceId)
            ->whereIn('id', $ids)
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

    /**
     * @param $exercice
     * @return StdClass|null
     */
    protected function convertExercice($exercice, $with = [])
    {
        if ($exercice == null) return null;

        $object = new StdClass();
        $object->id = $exercice->id;
        $object->exercice_categorie_id = $exercice->exercice_categorie_id;
        $object->designation = $exercice->designation;
        $object->date = $exercice->date;
        $object->heure = $exercice->heure;
        $object->duree = $exercice->duree;
        $object->lieu = $exercice->lieu;
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
        if ($localite == null) return null;

        $object = new StdClass();
        $object->id = $localite->id;

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
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;
        $object->sapeur_id = $sapeur->sapeur_id;
        $object->exercice_id = $sapeur->exercice_id;
        $object->convoque = $sapeur->convoque;
        $object->present = $sapeur->present;
        $object->remplace = $sapeur->remplace;
        $object->amende = $sapeur->amende;
        $object->excuse_type_id = $sapeur->excuse_type_id;

        return $object;
    }
    
    /**
     * @param $sapeur
     * @return StdClass|null
     */
    protected function convertSapeurWithExercicesInfos($sapeur)
    {
        if ($sapeur == null) return null;

        $object = new StdClass();
        $object->id = $sapeur->id;
        $object->sapeur_id = $sapeur->sapeur_id;
        $object->exercice_id = $sapeur->exercice_id;
        $object->convoque = $sapeur->convoque;
        $object->present = $sapeur->present;
        $object->remplace = $sapeur->remplace;
        $object->amende = $sapeur->amende;
        $object->excuse_type_id = $sapeur->excuse_type_id;
        $object->date = $sapeur->date;
        $object->heure = $sapeur->heure;
        $object->localite_id = $sapeur->localite_id;
        $object->communications = $sapeur->communications;
        $object->exercice_categorie_id = $sapeur->exercice_categorie_id;

        return $object;
    }
}
