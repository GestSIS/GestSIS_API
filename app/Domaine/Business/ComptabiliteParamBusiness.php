<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\Amende;
use App\Infrastructure\Models\Compte;
use App\Infrastructure\Models\EcritureCategorie;
use App\Infrastructure\Models\FraisAnnuel;
use App\Infrastructure\Models\FraisAnnuelType;
use App\Infrastructure\Models\IndemniteAnnuel;
use App\Infrastructure\Models\IndemniteAnnuelType;
use App\Infrastructure\Models\IndemniteExerciceType;
use App\Infrastructure\Models\IndemniteInterventionType;

class ComptabiliteParamBusiness
{
    public function updateAmendes($data)
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

    public function ajouterCategorie($data)
    {
        $categorie = new EcritureCategorie();
        $categorie->fill($data);
        $categorie->save();
        return $categorie;
    }

    public function modifierCategorie($id, $data)
    {
        EcritureCategorie::where('id', $id)->limit(1)->update($data);
        return EcritureCategorie::find($id);
    }

    public function supprimerCategorie($id)
    {
        //TODO: Implement this
    }

    public static function ajouterFraisAnnuel($data)
    {
        $frais = new FraisAnnuel();
        $frais->fill($data);
        $frais->save();
        return $frais;
    }

    public static function modifierFraisAnnuel($id, $data)
    {
        FraisAnnuel::where('id', $id)->limit(1)->update($data);
        return FraisAnnuel::find($id);
    }

    public static function supprimerFraisAnnuel($id)
    {
        FraisAnnuel::where('id', $id)->limit(1)->delete();
    }

    public static function ajouterFraisAnnuelType($data)
    {
        $frais = new FraisAnnuelType();
        $frais->fill($data);
        $frais->save();
        $object = $frais->toArray();
        $object['fraisAnnuels'] = [];
        return $object;
    }

    public static function modifierFraisAnnuelType($id, $data)
    {
        FraisAnnuelType::where('id', $id)->limit(1)->update($data);
        return FraisAnnuelType::with('fraisAnnuels')->find($id);
    }

    public static function supprimerFraisAnnuelType($id)
    {
        FraisAnnuelType::where('id', $id)->limit(1)->delete();
    }

    public static function ajouterIndemniteAnnuel($data)
    {
        $indemnite = new IndemniteAnnuel();
        $indemnite->fill($data);
        $indemnite->save();
        $object = $indemnite->toArray();
        $object['indemniteAnnuels'] = [];
        return $object;
    }

    public static function modifierIndemniteAnnuel($id, $data)
    {
        IndemniteAnnuel::where('id', $id)->limit(1)->update($data);
        return IndemniteAnnuel::find($id);
    }

    public static function supprimerIndemniteAnnuel($id)
    {
        IndemniteAnnuel::where('id', $id)->limit(1)->delete();
    }

    public static function ajouterIndemniteAnnuelType($data)
    {
        $indemnite = new IndemniteAnnuelType();
        $indemnite->fill($data);
        $indemnite->save();
        return $indemnite;
    }

    public static function modifierIndemniteAnnuelType($id, $data)
    {
        IndemniteAnnuelType::where('id', $id)->limit(1)->update($data);
        return IndemniteAnnuelType::with('indemniteAnnuels')->find($id);
    }

    public static function supprimerIndemniteAnnuelType($id)
    {
        IndemniteAnnuelType::where('id', $id)->limit(1)->delete();
    }

    public static function ajouterIndemniteExercice($data)
    {
        $parFonction = array_key_exists('par_fonction', $data) && $data['par_fonction'];
        if (!$parFonction) {
            $data['par_fonction'] = false;
        }
        $indemnite = IndemniteExerciceType::create($data);
        if ($parFonction) {
            if (!array_key_exists('fonctions', $data)) {
                $data['fonctions'] = [];
            }
            $indemnite->fonctions()->createMany($data['fonctions']);
        }
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

        if (!$parFonction) {
            $indemnite->fonctions()->delete();
        } else {
            if (!array_key_exists('fonctions', $data)) {
                $data['fonctions'] = [];
            }
            $indemnite->fonctions()->whereNotIn('fonction_id', array_filter(array_map(fn ($f) => $f['fonction_id'], $data['fonctions']), fn ($f) => !is_null($f)))->delete();
            foreach ($data['fonctions'] as $f) {
                $indemnite->fonctions()->updateOrCreate(['fonction_id' => $f['fonction_id']], $f);
            }
        }

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
        $indemnite = IndemniteInterventionType::find($id);
        $indemnite->update($data);
        if (!$parFonction) {
            $indemnite->fonctions()->delete();
        } else {
            $indemnite->fonctions()->whereNotIn('fonction_id', array_filter(array_map(fn ($f) => $f['fonction_id'], $data['fonctions']), fn ($f) => !is_null($f)))->delete();
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
        //TODO: Not implemented now
    }
}
