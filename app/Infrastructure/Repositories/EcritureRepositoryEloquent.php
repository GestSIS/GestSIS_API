<?php


namespace App\Infrastructure\Repositories;

use App\Domaine\Business\ImputationBusiness;
use App\Domaine\SPI\EcritureRepository;
use App\Infrastructure\Models\Ecriture;
use Illuminate\Support\Facades\DB;
use StdClass;

class EcritureRepositoryEloquent implements EcritureRepository
{

    public function listeAllEcritureForExerciceComptableById($exerciceComptableId)
    {
        return $this->convertCollectionOfEcritures(
            Ecriture
                ::where('exercice_comptable_id', $exerciceComptableId)
                ->get()
        );
    }

    public function listeAmendeForExerciceComptableById($exerciceComptableId)
    {
        return $this->convertCollectionOfEcritures(
            Ecriture
                ::where('exercice_comptable_id', $exerciceComptableId)
                ->where('module', ImputationBusiness::ECRITURE_MODULE_AMENDE)
                ->get()
        );
    }

    public function listeEcritureDiversForExerciceComptableById($exerciceComptableId)
    {
        return $this->convertCollectionOfEcritures(
            Ecriture
                ::where('exercice_comptable_id', $exerciceComptableId)
                ->where('module', ImputationBusiness::ECRITURE_MODULE_DIVERS)
                ->get()
        );
    }

    public function listeEcritureForCompteAndExerciceComptableById($compteId, $exerciceComptableId)
    {
        return $this->convertCollectionOfEcritures(
            Ecriture::where([
                ['exercice_comptable_id', '=', $exerciceComptableId],
                ['compte_id', '=', $compteId]
            ])->get()
        );
    }

    public function listeEcritureForExercice($exercice_id)
    {
        return $this->convertCollectionOfEcritures(
            Ecriture
                ::where('exercice_id', $exercice_id)
                ->get()
        );
    }

    public function listeEcritureForIntervention($intervention_id)
    {
        return $this->convertCollectionOfEcritures(
            Ecriture
                ::where('intervention_id', $intervention_id)
                ->get()
        );
    }

    public function listeEcrituresAnnuelsForExerciceComptableById($exerciceComptableId)
    {
        return $this->convertCollectionOfEcritures(
            Ecriture
                ::where('exercice_comptable_id', $exerciceComptableId)
                ->where('module', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL)
                ->get()
        );
    }

    public function computeEcritureForPersonalDecompte($exerciceComptableId)
    {
        $ecritures = DB::table('ecritures')
            ->join('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
            ->join('ecriture_categories', 'ecritures.ecriture_categorie_id', '=', 'ecriture_categories.id')
            ->join('type_unites', 'ecritures.type_unite_id', '=', 'type_unites.id')
            ->join('civilites', 'sapeurs.civilite_id', '=', 'civilites.id')
            ->where('ecritures.exercice_comptable_id', $exerciceComptableId)
            ->select(
                'ecritures.*',
                DB::raw('concat(sapeurs.nom, " ", sapeurs.prenom) as sapeur'),
                'ecriture_categories.tri',
                'ecriture_categories.designation AS categorie',
                'type_unites.abreviation as unite',
                'civilites.forme_politesse as civilite'
            )
            ->orderBy('sapeur')
            ->orderBy('ecriture_categories.tri', 'ASC')
            ->orderBy('ecritures.date')
            ->orderBy('ecritures.heure')
            ->get();

        $temp = $this;
        return $ecritures
            ->map(function ($ecriture) use ($temp) {
                return $temp->convertEcritureForDecomptes($ecriture);
            })->toArray();
    }

    /**
     * @param $ecriture
     */
    public function persisteNewEcriture($ecriture)
    {
        if (!array_key_exists('tarif_min', $ecriture)) $ecriture['tarif_min'] = null;
        if (!array_key_exists('tarif_min_pour', $ecriture)) $ecriture['tarif_min_pour'] = null;
        if (!array_key_exists('tarif_min_pro_rata', $ecriture)) $ecriture['tarif_min_pro_rata'] = null;
        if (!array_key_exists('taux', $ecriture)) $ecriture['taux'] = null;
        if (!array_key_exists('taux_description', $ecriture)) $ecriture['taux_description'] = null;

        if (!array_key_exists('exercice_comptable_id', $ecriture)) $ecriture['exercice_comptable_id'] = null;
        if (!array_key_exists('intervention_id', $ecriture)) $ecriture['intervention_id'] = null;
        if (!array_key_exists('exercice_id', $ecriture)) $ecriture['exercice_id'] = null;
        if (!array_key_exists('decompte_id', $ecriture)) $ecriture['decompte_id'] = null;
        if (!array_key_exists('date', $ecriture)) $ecriture['date'] = null;
        if (!array_key_exists('heure', $ecriture)) $ecriture['heure'] = null;

        $model = new Ecriture();
        $model->fill($ecriture);
        $model->save();
    }

    protected function convertCollectionOfEcritures($ecritures)
    {
        $temp = $this;
        return $ecritures
            ->map(function ($ecriture) use ($temp) {
                return $temp->convertEcriture($ecriture);
            })->toArray();
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
        $object->type_unite_id = $ecriture->type_unite_id;
        $object->quantite = $ecriture->quantite;
        $object->tarif = $ecriture->tarif;
        $object->tarif_min = $ecriture->tarif_min;
        $object->tarif_min_pour = $ecriture->tarif_min_pour;
        $object->tarif_min_pro_rata = $ecriture->tarif_min_pro_rata;
        $object->taux = $ecriture->taux;
        $object->sapeur_id = $ecriture->sapeur_id;
        $object->exercice_comptable_id = $ecriture->exercice_comptable_id;
        $object->intervention_id = $ecriture->intervention_id;
        $object->exercice_id = $ecriture->exercice_id;

        $object->compte_id = $ecriture->compte_id;
        $object->ecriture_categorie_id = $ecriture->ecriture_categorie_id;
        $object->date = $ecriture->date;
        $object->heure = $ecriture->heure;
        $object->decompte_id = $ecriture->decompte_id;

        $object->type = $ecriture->type;

        return $object;
    }

    /**
     * @param $ecriture
     * @return stdClass|null
     */
    protected function convertEcritureForDecomptes($ecriture)
    {
        if ($ecriture == null) return null;

        $object = new StdClass();
        $object->id = $ecriture->id;
        $object->designation = $ecriture->designation;
        $object->total = $ecriture->total;
        $object->type_unite_id = $ecriture->type_unite_id;
        $object->quantite = $ecriture->quantite;
        $object->tarif = $ecriture->tarif;
        $object->tarif_min = $ecriture->tarif_min;
        $object->tarif_min_pour = $ecriture->tarif_min_pour;
        $object->tarif_min_pro_rata = $ecriture->tarif_min_pro_rata;
        $object->taux = $ecriture->taux;
        $object->sapeur_id = $ecriture->sapeur_id;
        $object->exercice_comptable_id = $ecriture->exercice_comptable_id;
        $object->intervention_id = $ecriture->intervention_id;
        $object->exercice_id = $ecriture->exercice_id;
        $object->compte_id = $ecriture->compte_id;
        $object->ecriture_categorie_id = $ecriture->ecriture_categorie_id;
        $object->date = $ecriture->date;
        $object->heure = $ecriture->heure;
        $object->sapeur = $ecriture->sapeur;
        $object->categorie = $ecriture->categorie;
        $object->tri = $ecriture->tri;
        $object->unite = $ecriture->unite;
        $object->civilite = $ecriture->civilite;

        $object->type = $ecriture->type;

        return $object;
    }
}
