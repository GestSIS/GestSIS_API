<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Exercice;
use App\Models\Intervention;
use App\Models\Travail;
use App\Models\TravailType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TravauxBusiness
{
    public const TRAVAIL_STATUT_SAISI = 0;
    public const TRAVAIL_STATUT_VALIDE = 1;
    public const TRAVAIL_STATUT_IMPUTE = 2;
    public const TRAVAIL_STATUT_INVALIDE = -1;

    /**
     * @param array<int, array<string, mixed>> $travaux
     * @return array<int, Travail>
     */
    public static function ajouter(array $travaux, int $auteurId, bool $hasSaisieCommunePermission, bool $hasValidationExercice = false, bool $hasValidationIntervention = false): array
    {
        // Check que l'auteur a saisie commune si nécessaire
        if (collect($travaux)->contains(fn($t) => $t['sapeur_id'] !== $auteurId) && !$hasSaisieCommunePermission) {
            throw new ArrayException([], 'Permission insufisante pour saisir des travaux pour d\'autres sapeurs');
        }

        // Contrôle de tous les liens avant la moindre création
        $prepares = collect($travaux)->map(function ($travail) use ($hasValidationExercice, $hasValidationIntervention) {
            $travail = self::controlerLien($travail, TravailType::findOrFail($travail['travail_type_id']));
            $evenementValide = self::statutEvenement($travail['exercice_id'], $travail['intervention_id'], $hasValidationExercice, $hasValidationIntervention);
            $travail['statut'] = $evenementValide === true ? self::TRAVAIL_STATUT_VALIDE : self::TRAVAIL_STATUT_SAISI;
            return $travail;
        });

        // Création des travaux
        return DB::transaction(fn() => $prepares->map(fn($travail) => Travail::create([
            ...$travail,
            'auteur_id' => $auteurId,
            'date_demande' => Carbon::now()->toDateString(),
            'justification' => ''
        ]))->all());
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function modifier(int $travailId, array $data, ?int $sapeurId, bool $hasValidationExercice = false, bool $hasValidationIntervention = false): Travail
    {
        $travail = Travail::find($travailId);
        if ($travail === null) {
            throw new ArrayException([], 'Travail introuvable');
        }

        self::controlerModifiable($travail, $sapeurId, $hasValidationExercice, $hasValidationIntervention);

        // Le nouvel événement lié doit lui aussi accepter la fiche
        $data = self::controlerLien($data, TravailType::findOrFail($data['travail_type_id']));
        self::statutEvenement($data['exercice_id'], $data['intervention_id'], $hasValidationExercice, $hasValidationIntervention);

        $travail->fill($data);
        $travail->save();
        return $travail;
    }

    public static function supprimer(int $travailId, ?int $sapeurId, bool $hasValidationExercice = false, bool $hasValidationIntervention = false): string
    {
        $travail = Travail::find($travailId);
        if ($travail === null) {
            throw new ArrayException([], 'Travail introuvable');
        }

        self::controlerModifiable($travail, $sapeurId, $hasValidationExercice, $hasValidationIntervention);

        Travail::whereId($travailId)->delete();
        return 'ok';
    }

    /**
     * Une fiche liée à un événement se modifie selon le statut de l'événement ;
     * le validateur de l'événement n'est soumis ni à l'auteur ni au statut de la fiche.
     */
    private static function controlerModifiable(Travail $travail, ?int $sapeurId, bool $hasValidationExercice, bool $hasValidationIntervention): void
    {
        if ($travail->statut === self::TRAVAIL_STATUT_IMPUTE) {
            throw new ArrayException([], 'Travail déjà imputé');
        }

        self::statutEvenement($travail->exercice_id, $travail->intervention_id, $hasValidationExercice, $hasValidationIntervention);

        $estValidateurEvenement = ($travail->exercice_id !== null && $hasValidationExercice)
            || ($travail->intervention_id !== null && $hasValidationIntervention);
        if ($estValidateurEvenement) {
            return;
        }

        if ($travail->statut !== self::TRAVAIL_STATUT_SAISI) {
            throw new ArrayException([], 'Travail déjà traité');
        }

        if ($travail->auteur_id !== $sapeurId) {
            throw new ArrayException([], 'Impossible de modifier un travail dont vous n\'êtes pas l\'auteur');
        }
    }

    /**
     * Contrôle la cohérence du lien avec le type de travail et impose
     * l'exercice comptable de l'événement lié.
     *
     * @param array<string, mixed> $travail
     * @return array<string, mixed>
     */
    private static function controlerLien(array $travail, TravailType $type): array
    {
        $travail['exercice_id'] = $travail['exercice_id'] ?? null;
        $travail['intervention_id'] = $travail['intervention_id'] ?? null;

        if ($travail['exercice_id'] !== null && $travail['intervention_id'] !== null) {
            throw new ArrayException([], 'Une fiche ne peut être liée qu\'à un seul événement');
        }

        if ($travail['exercice_id'] !== null && !$type->lien_exercice) {
            throw new ArrayException([], 'Ce type de travail ne peut pas être lié à un exercice');
        }

        if ($travail['intervention_id'] !== null && !$type->lien_intervention) {
            throw new ArrayException([], 'Ce type de travail ne peut pas être lié à une intervention');
        }

        if ($travail['exercice_id'] === null && $travail['intervention_id'] === null && $type->lien_obligatoire) {
            throw new ArrayException([], 'Ce type de travail doit être lié à un exercice ou une intervention');
        }

        if ($travail['exercice_id'] !== null) {
            $travail['exercice_comptable_id'] = Exercice::findOrFail($travail['exercice_id'])->exercice_comptable_id;
        } elseif ($travail['intervention_id'] !== null) {
            $travail['exercice_comptable_id'] = Intervention::findOrFail($travail['intervention_id'])->exercice_comptable_id;
        }

        return $travail;
    }

    /**
     * Vérifie que l'événement lié accepte une modification de ses fiches.
     *
     * @return bool|null true si l'événement est validé (et l'appelant a le droit), false s'il est modifiable normalement, null sans lien
     * @throws ArrayException
     */
    private static function statutEvenement(?int $exerciceId, ?int $interventionId, bool $hasValidationExercice, bool $hasValidationIntervention): ?bool
    {
        if ($exerciceId !== null) {
            $statut = Exercice::findOrFail($exerciceId)->statut;
            if ($statut === ExerciceBusiness::EXERCICE_STATUT_ANNULE) {
                throw new ArrayException([], 'Impossible de modifier les fiches d\'un exercice annulé');
            }
            return self::controlerStatut($statut === ExerciceBusiness::EXERCICE_STATUT_IMPUTE, $statut === ExerciceBusiness::EXERCICE_STATUT_VALIDE, $hasValidationExercice);
        }

        if ($interventionId !== null) {
            $statut = Intervention::findOrFail($interventionId)->statut;
            return self::controlerStatut($statut === InterventionBusiness::INTERVENTION_STATUT_IMPUTE, $statut === InterventionBusiness::INTERVENTION_STATUT_VALIDE, $hasValidationIntervention);
        }

        return null;
    }

    private static function controlerStatut(bool $estImpute, bool $estValide, bool $hasValidation): bool
    {
        if ($estImpute) {
            throw new ArrayException([], 'Impossible de modifier les fiches d\'un événement déjà imputé');
        }

        if ($estValide && !$hasValidation) {
            throw new ArrayException([], 'Permissions insuffisantes');
        }

        return $estValide;
    }

    public static function review($travailId, $accepte, $justification, $quantite)
    {
        $travail = Travail::find($travailId);
        if ($travail === null) {
            throw new ArrayException([], 'Travail introuvable');
        }

        if ($travail->statut === self::TRAVAIL_STATUT_IMPUTE) {
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

    public static function cancelReview($travailId)
    {
        $travail = Travail::find($travailId);
        if ($travail === null) {
            throw new ArrayException([], 'Travail introuvable');
        }

        if ($travail->statut === self::TRAVAIL_STATUT_IMPUTE) {
            throw new ArrayException([], 'Travail déjà imputé');
        }

        $travail->statut = self::TRAVAIL_STATUT_SAISI;
        $travail->justification = '';
        $travail->save();
        return $travail;
    }
}
