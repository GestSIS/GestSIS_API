<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Travail;
use Carbon\Carbon;

class TravauxBusiness
{
    public const TRAVAIL_STATUT_SAISI = 0;
    public const TRAVAIL_STATUT_VALIDE = 1;
    public const TRAVAIL_STATUT_IMPUTE = 2;
    public const TRAVAIL_STATUT_INVALIDE = -1;

    public function ajouter($travaux, $auteurId, $hasSaisieCommunePermission)
    {
        // Check que l'auteur a saisie commune si nécessaire
        if (count(array_filter($travaux, fn ($t) => $t['sapeur_id'] != $auteurId)) > 0 && !$hasSaisieCommunePermission) {
            throw new ArrayException([], 'Permission insufisante pour saisir des travaux pour d\'autres sapeurs');
        }

        // Création des travaux
        $newTravaux = [];
        foreach ($travaux as $travail) {
            $newTravaux[] = Travail::create([
                ...$travail,
                'auteur_id' => $auteurId,
                'statut' => self::TRAVAIL_STATUT_SAISI,
                'date_demande' => Carbon::now(),
                'justification' => ''
            ]);
        }

        return $newTravaux;
    }

    public function modifier($travailId, $data, $sapeurId)
    {
        // Check status du travail
        $travail = Travail::find($travailId);
        if ($travail == null) {
            throw new ArrayException([], 'Travail introuvable');
        }

        if ($travail->statut != self::TRAVAIL_STATUT_SAISI) {
            throw new ArrayException([], 'Travail déjà traité');
        }

        if ($travail->auteur_id != $sapeurId) {
            throw new ArrayException([], 'Impossible de modifier un travail dont vous n\'êtes pas l\'auteur');
        }

        $travail->fill($data);
        $travail->save();
        return $travail;
    }

    public function supprimer($travailId, $sapeurId)
    {
        // Check status du travail  
        $travail = Travail::find($travailId);
        if ($travail == null) {
            throw new ArrayException([], 'Travail introuvable');
        }

        if ($travail->statut != self::TRAVAIL_STATUT_SAISI) {
            throw new ArrayException([], 'Travail déjà traité');
        }

        if ($travail->auteur_id != $sapeurId) {
            throw new ArrayException([], 'Impossible de modifier un travail dont vous n\'êtes pas l\'auteur');
        }
        Travail::where('id', '=', $travailId)->delete();
        return 'ok';
    }

    public function review($travailId, $accepte, $justification, $quantite)
    {
        $travail = Travail::find($travailId);
        if ($travail == null) {
            throw new ArrayException([], 'Travail introuvable');
        }

        if ($travail->statut == self::TRAVAIL_STATUT_IMPUTE) {
            throw new ArrayException([], 'Travail déjà imputé');
        }

        $travail->statut = $accepte ? self::TRAVAIL_STATUT_VALIDE : self::TRAVAIL_STATUT_INVALIDE;
        $travail->justification = $justification;
        if ($quantite) {
            $travail->quantite = $quantite;
        }
        $travail->save();
        return $travail;
    }

    public function cancelReview($travailId)
    {
        $travail = Travail::find($travailId);
        if ($travail == null) {
            throw new ArrayException([], 'Travail introuvable');
        }

        if ($travail->statut == self::TRAVAIL_STATUT_IMPUTE) {
            throw new ArrayException([], 'Travail déjà imputé');
        }

        $travail->statut = self::TRAVAIL_STATUT_SAISI;
        $travail->justification = '';
        $travail->save();
        return $travail;
    }
}
