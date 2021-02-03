<?php


namespace App\Domaine\Business;

use App\Infrastructure\Models\Compte;
use App\Infrastructure\Models\EcritureCategorie;
use App\Infrastructure\Models\FraisAnnuelType;
use App\Infrastructure\Models\IndemniteAnnuelType;
use App\Infrastructure\Models\IndemniteExerciceType;
use App\Infrastructure\Models\IndemniteInterventionType;

class ComptabiliteParamBusiness
{
    
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
        $frais = new FraisAnnuelType();
        $frais->fill($data);
        $frais->save();
        return $frais;
    }

    public static function modifierFraisAnnuel($id, $data)
    {
        FraisAnnuelType::where('id', $id)->limit(1)->update($data);
        return FraisAnnuelType::find($id);
    }

    public static function supprimerFraisAnnuel($id)
    {
        //TODO: Not implemented now
    }

    public static function ajouterIndemniteAnnuel($data)
    {
        $indemnite = new IndemniteAnnuelType();
        $indemnite->fill($data);
        $indemnite->save();
        return $indemnite;
    }

    public static function modifierIndemniteAnnuel($id, $data)
    {
        IndemniteAnnuelType::where('id', $id)->limit(1)->update($data);
        return IndemniteAnnuelType::find($id);
    }

    public static function supprimerIndemniteAnnuel($id)
    {
        //TODO: Not implemented now
    }

    public static function ajouterIndemniteExercice($data)
    {
        $parFonction = array_key_exists('par_fonction', $data) && $data['par_fonction'];
        if (!$parFonction) {
            $data['par_fonction'] = false;
        }
        $indemnite = IndemniteExerciceType::create($data);
        if ($parFonction) {
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
            return "Not par fonction";
            $indemnite->fonctions()->delete();
        } else {
            $indemnite->fonctions()->whereNotIn('fonction_id', array_filter(array_map(fn($f) => $f['fonction_id'], $data['fonctions']),fn($f) => !is_null($f)))->delete();
            foreach ($data['fonctions'] as $f) {
                $indemnite->fonctions()->updateOrCreate(['fonction_id' => $f['fonction_id']], $f);
            }
        }

        $indemnite->fonctions;
        return $indemnite;
    }

    public static function supprimerIndemniteExercice($id)
    {
        //TODO: Not implemented now
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
            $indemnite->fonctions()->whereNotIn('fonction_id', array_filter(array_map(fn($f) => $f['fonction_id'], $data['fonctions']),fn($f) => !is_null($f)))->delete();
            foreach ($data['fonctions'] as $f) {
                $indemnite->fonctions()->updateOrCreate(['fonction_id' => $f['fonction_id']], $f);
            }
        }
        
        $indemnite->fonctions;
        return $indemnite;
    }

    public static function supprimerIndemniteIntervention($id)
    {
        //TODO: Not implemented now
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
