<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\InvalidActionException;
use App\Models\Amende;
use App\Models\AvsParam;
use App\Models\Compte;
use App\Models\Ecriture;
use App\Models\EcritureCategorie;
use App\Models\FraisIndemniteAnnuel;
use App\Models\FraisIndemniteAnnuelType;
use App\Models\HeureExerciceType;
use App\Models\IndemniteCoursFonction;
use App\Models\IndemniteCoursType;
use App\Models\IndemniteExerciceFonction;
use App\Models\IndemniteExerciceType;
use App\Models\IndemniteInterventionType;

class ComptabiliteParamBusiness
{
    public static function updateAmendes($data)
    {
        $compteId = $data['compte_id'];
        $ecitureCategorieId = $data['ecriture_categorie_id'];

        $index = 1;
        foreach ($data['amendes'] as $amende) {
            $amende['ordre'] = $index;
            $amende['compte_id'] = $compteId;
            $amende['ecriture_categorie_id'] = $ecitureCategorieId;
            Amende::updateOrCreate(['ordre' => $index], $amende);
            $index++;
        }
        Amende::where('ordre', '>=', $index)->delete();
        return Amende::all();
    }

    public static function ajouterCategorie($data)
    {
        $categorie = new EcritureCategorie();
        $categorie->fill($data);
        $categorie->save();
        return $categorie;
    }

    public static function modifierCategorie($id, $data)
    {
        EcritureCategorie::where('id', $id)->limit(1)->update($data);
        return EcritureCategorie::find($id);
    }

    public static function supprimerCategorie($id)
    {
        if (Ecriture::where('ecriture_categorie_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer une catégorie lié à des écritures");
        }
        if (IndemniteExerciceType::where('ecriture_categorie_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer une catégorie lié à type d'indemnité d'exercice");
        }
        if (IndemniteInterventionType::where('ecriture_categorie_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer une catégorie lié à type d'indemnité d'intervention");
        }
        if (FraisIndemniteAnnuelType::where('ecriture_categorie_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer une catégorie lié à type d'indemnité annuel");
        }
        if (HeureExerciceType::where('ecriture_categorie_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer une catégorie lié à type d'heure supplémentaires");
        }
        if (AvsParam::where('ecriture_categorie_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer une catégorie lié aux paramètres d'imputation AVS/AC");
        }
        if (Amende::where('ecriture_categorie_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer une catégorie lié à une amende");
        }
        EcritureCategorie::where('id', '=', $id)->limit(1)->delete();
        return true;
    }

    public static function ajouterFraisIndemniteAnnuel($data)
    {
        // Pour les unités mensuelles, forcer la quantité à 12
        if (isset($data['type_unite_id']) && $data['type_unite_id'] == ImputationBusiness::UNITE_MOIS) {
            $data['quantite'] = 12;
        }

        $indemnite = new FraisIndemniteAnnuel();
        $indemnite->fill($data);
        $indemnite->save();
        return $indemnite;
    }

    public static function modifierFraisIndemniteAnnuel($id, $data)
    {
        // Pour les unités mensuelles, forcer la quantité à 12
        if (isset($data['type_unite_id']) && $data['type_unite_id'] == ImputationBusiness::UNITE_MOIS) {
            $data['quantite'] = 12;
        }

        FraisIndemniteAnnuel::where('id', $id)->limit(1)->update($data);
        return FraisIndemniteAnnuel::find($id);
    }

    public static function supprimerFraisIndemniteAnnuel($id)
    {
        FraisIndemniteAnnuel::where('id', $id)->limit(1)->delete();
    }

    public static function ajouterFraisIndemniteAnnuelType($data)
    {
        $indemnite = new FraisIndemniteAnnuelType();
        $indemnite->fill($data);
        $indemnite->save();
        $object = $indemnite->toArray();
        $object['frais_indemnite_annuels'] = [];
        return $object;
    }

    public static function modifierFraisIndemniteAnnuelType($id, $data)
    {
        FraisIndemniteAnnuelType::where('id', $id)->limit(1)->update($data);
        return FraisIndemniteAnnuelType::with('fraisIndemniteAnnuels')->find($id);
    }

    public static function supprimerFraisIndemniteAnnuelType($id)
    {
        FraisIndemniteAnnuelType::where('id', $id)->limit(1)->delete();
    }

    public static function ajouterIndemniteExercice($data)
    {
        $parFonction = array_key_exists('par_fonction', $data) && $data['par_fonction'];
        if (!$parFonction) {
            $data['par_fonction'] = false;
        }
        $indemnite = IndemniteExerciceType::create($data);
        if (!array_key_exists('fonctions', $data)) {
            $data['fonctions'] = [];
        }
        $indemnite->fonctions()->createMany($data['fonctions']);
        $indemnite->fonctions;
        return $indemnite;
    }

    public static function modifierIndemniteExercice($id, $data)
    {
        $parFonction = array_key_exists('par_fonction', $data) && $data['par_fonction'];
        if (!$parFonction) {
            $data['par_fonction'] = false;
        }
        $indemnite = IndemniteExerciceType::find($id);
        $indemnite->update($data);

        $indemnite->fonctions()->delete();
        if (!array_key_exists('fonctions', $data)) {
            $data['fonctions'] = [];
        }
        $indemnite->fonctions()->createMany($data['fonctions']);

        $indemnite->fonctions;
        return $indemnite;
    }

    public static function supprimerIndemniteExercice($id)
    {
        IndemniteExerciceType::where('id', $id)->limit(1)->delete();
    }

    public static function ajouterIndemniteIntervention($data)
    {
        $parFonction = array_key_exists('par_fonction', $data) && $data['par_fonction'];
        if (array_key_exists('phase_id', $data) && $data['phase_id'] == 0) {
            $data['phase_id'] = NULL;
        }
        if (array_key_exists('tarif_pro_rata', $data) && ($data['tarif_pro_rata'] == 0 || $data['tarif_pro_rata'] == null)) {
            $data['tarif_pro_rata'] = false;
        }
        if (!$parFonction) {
            $data['par_fonction'] = false;
        }
        $indemnite = IndemniteInterventionType::create($data);
        if ($parFonction) {
            $indemnite->fonctions()->createMany($data['fonctions']);
        }
        $indemnite->fonctions;
        return $indemnite;
    }

    public static function modifierIndemniteIntervention($id, $data)
    {
        $parFonction = array_key_exists('par_fonction', $data) && $data['par_fonction'];
        if (array_key_exists('phase_id', $data) && $data['phase_id'] == 0) {
            $data['phase_id'] = NULL;
        }
        if (array_key_exists('tarif_pro_rata', $data) && ($data['tarif_pro_rata'] == 0 || $data['tarif_pro_rata'] == null)) {
            $data['tarif_pro_rata'] = false;
        }
        $indemnite = IndemniteInterventionType::find($id);
        $indemnite->update($data);
        if (!$parFonction) {
            $indemnite->fonctions()->delete();
        } else {
            $indemnite->fonctions()->whereNotIn('fonction_id', array_filter(array_map(fn($f) => $f['fonction_id'], $data['fonctions']), fn($f) => !is_null($f)))->delete();
            foreach ($data['fonctions'] as $f) {
                $indemnite->fonctions()->updateOrCreate(['fonction_id' => $f['fonction_id']], $f);
            }
        }

        $indemnite->fonctions;
        return $indemnite;
    }

    public static function supprimerIndemniteIntervention($id)
    {
        IndemniteInterventionType::where('id', $id)->limit(1)->delete();
    }

    public static function ajouterCompte($data)
    {
        $indemnite = Compte::create($data);

        return $indemnite;
    }

    public static function modifierCompte($id, $data)
    {
        $indemnite = Compte::find($id);
        $indemnite->update($data);
        return $indemnite;
    }

    public static function supprimerCompte($id)
    {
        if (Ecriture::where('compte_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer un compte lié à des écritures");
        }
        if (IndemniteExerciceFonction::where('compte_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer un compte lié à type d'indemnité d'exercice");
        }
        if (IndemniteInterventionType::where('compte_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer un compte lié à type d'indemnité d'intervention");
        }
        if (FraisIndemniteAnnuelType::where('compte_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer un compte lié à type d'indemnité annuel");
        }
        if (HeureExerciceType::where('compte_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer un compte lié à type d'heure supplémentaires");
        }
        if (AvsParam::where('compte_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer un compte lié aux paramètres d'imputation AVS/AC");
        }
        if (Amende::where('compte_id', '=', $id)->count() > 0) {
            throw new InvalidActionException(message: "Impossible de supprimer un compte lié à une amende");
        }
        Compte::where('id', '=', $id)->limit(1)->delete();
        return true;
    }

    public static function ajouterIndemniteCoursType($data)
    {
        $indemnite = IndemniteCoursType::create($data);
        if (!array_key_exists('fonctions', $data)) {
            $data['fonctions'] = [];
        }
        $indemnite->fonctions()->createMany($data['fonctions']);
        $indemnite->fonctions;
        return $indemnite;
    }

    public static function modifierIndemniteCoursType($id, $data)
    {
        $indemnite = IndemniteCoursType::find($id);
        $indemnite->update($data);

        $indemnite->fonctions()->delete();
        if (!array_key_exists('fonctions', $data)) {
            $data['fonctions'] = [];
        }
        $indemnite->fonctions()->createMany($data['fonctions']);

        $indemnite->fonctions;
        return $indemnite;
    }

    public static function supprimerIndemniteCoursType($id)
    {
        IndemniteCoursFonction::where('indemnite_cours_id', $id)->delete();
        IndemniteCoursType::where('id', $id)->limit(1)->delete();
    }
}
