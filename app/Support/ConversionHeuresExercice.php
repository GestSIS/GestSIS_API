<?php

namespace App\Support;

/**
 * Correspondance entre les heures supplémentaires d'exercice et les fiches de travail,
 * utilisée par la commande dbs:migrer-heures-exercice. Fonctions pures, sans accès à la base.
 */
final class ConversionHeuresExercice
{
    /** Module comptable des fiches de travail (ImputationBusiness::ECRITURE_MODULE_FICHE_TRAVAIL) */
    public const MODULE_FICHE_TRAVAIL = 6;

    private const SUFFIXE_HOMONYME = ' (exercice)';

    /**
     * Type de fiche (liable obligatoirement à un exercice) et son unique ligne de tarif.
     * Accepte un type d'heure, ou une heure dont le type n'existe plus (même colonnes).
     *
     * @param array<string, mixed> $heureType
     * @return array{type: array<string, mixed>, ligne: array<string, mixed>}
     */
    public static function typeDepuisHeureType(array $heureType, string $designation): array
    {
        return [
            'type' => [
                'designation' => $designation,
                'actif' => true,
                'ecriture_categorie_id' => $heureType['ecriture_categorie_id'],
                'type_unite_id' => $heureType['type_unite_id'],
                'lien_exercice' => true,
                'lien_intervention' => false,
                'lien_obligatoire' => true,
            ],
            'ligne' => [
                'type' => $heureType['type'],
                'tarif' => $heureType['montant'],
                'compte_id' => $heureType['compte_id'],
                'fonction_id' => null,
            ],
        ];
    }

    /**
     * Désignation du type converti : inchangée si elle est libre, suffixée sinon.
     *
     * @param array<int, string> $designationsExistantes
     */
    public static function designationLibre(string $designation, array $designationsExistantes): string
    {
        return in_array($designation, $designationsExistantes, true) ? $designation . self::SUFFIXE_HOMONYME : $designation;
    }

    /**
     * Statut de la fiche selon celui de l'exercice (ExerciceBusiness → TravauxBusiness).
     */
    public static function statutDepuisExercice(int $statutExercice): int
    {
        return match ($statutExercice) {
            4 => 2, // imputé → imputée
            3 => 1, // validé → validée
            0 => -1, // annulé → invalidée
            default => 0,
        };
    }

    /**
     * @param array<string, mixed> $heure
     * @param array<string, mixed> $exercice
     * @return array<string, mixed>
     */
    public static function travailDepuisHeure(array $heure, array $exercice, int $travailTypeId): array
    {
        return [
            'designation' => $heure['designation'],
            'quantite' => $heure['quantite'],
            'sapeur_id' => $heure['sapeur_id'],
            'auteur_id' => $heure['sapeur_id'],
            'travail_type_id' => $travailTypeId,
            'exercice_id' => $exercice['id'],
            'exercice_comptable_id' => $exercice['exercice_comptable_id'],
            'date' => $exercice['date'],
            'date_demande' => $heure['created_at'] === null ? $exercice['date'] : substr((string) $heure['created_at'], 0, 10),
            'justification' => '',
            'statut' => self::statutDepuisExercice((int) $exercice['statut']),
        ];
    }

    /**
     * L'écriture d'une heure sup porte `{désignation de l'exercice} - {désignation de l'heure}` et sa quantité.
     * Le début n'est pas comparé : la désignation de l'exercice a pu changer depuis l'imputation.
     *
     * @param array<string, mixed> $ecriture
     * @param array<string, mixed> $heure
     */
    public static function ecritureCorrespond(array $ecriture, array $heure): bool
    {
        return str_ends_with((string) $ecriture['designation'], self::suffixe($heure))
            && round((float) $ecriture['quantite'], 2) === round((float) $heure['quantite'], 2);
    }

    /**
     * Champs à mettre à jour pour aligner l'écriture d'une heure sup sur celle d'une fiche liée :
     * désignation de l'exercice seule, nature du travail en complément.
     *
     * @param array<string, mixed> $ecriture
     * @param array<string, mixed> $heure
     * @return array<string, mixed>
     */
    public static function ecritureAlignee(array $ecriture, array $heure, string $typeDesignation, int $travailId): array
    {
        $designation = (string) $ecriture['designation'];

        return [
            'travail_id' => $travailId,
            'module' => self::MODULE_FICHE_TRAVAIL,
            'designation' => substr($designation, 0, strlen($designation) - strlen(self::suffixe($heure))),
            'complement' => $typeDesignation . ' - ' . $heure['designation'],
        ];
    }

    /**
     * @param array<string, mixed> $heure
     */
    private static function suffixe(array $heure): string
    {
        return ' - ' . $heure['designation'];
    }
}
