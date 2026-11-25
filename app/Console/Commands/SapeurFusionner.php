<?php

namespace App\Console\Commands;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\Exceptions\NotReportableException;
use App\Models\Mutation;
use App\Models\Sapeur;
use App\Support\Sis;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Fusion d'une fiche civil dans une fiche sapeur : doublons créés avant que
 * sapeur et civil puissent coexister sur une même fiche. Tout ce qui référence
 * le civil est rattaché au sapeur, puis la fiche civil est supprimée.
 */
class SapeurFusionner extends Command
{
    protected $signature = 'sapeur:fusionner
        {sapeur_id : Fiche sapeur, conservée}
        {civil_id : Fiche civil, supprimée après la fusion}
        {--sis= : SIS concerné}
        {--dry-run : Affiche le bilan sans rien enregistrer}';

    protected $description = 'Fusionne une fiche civil dans une fiche sapeur (doublons d\'avant la refonte sapeur/civil)';

    /**
     * Colonnes qui référencent une fiche, par table
     */
    public const COLONNES = [
        'absences' => ['sapeur_id'],
        'article_attributions' => ['sapeur_id', 'attribue_par_sapeur_id', 'cloture_par_sapeur_id'],
        'articles' => ['sapeur_id', 'ajoute_par_sapeur_id', 'supprime_par_sapeur_id'],
        'controle_execs' => ['executed_by'],
        'controles_medicaux' => ['sapeur_id'],
        'cours_sapeur' => ['sapeur_id'],
        'ecritures' => ['sapeur_id'],
        'exercice_sapeur' => ['sapeur_id'],
        'fonction_sapeur' => ['sapeur_id'],
        'grade_sapeur' => ['sapeur_id'],
        'groupe_sapeur' => ['sapeur_id'],
        'ics_tokens' => ['sapeur_id'],
        'intervention_sapeur' => ['sapeur_id'],
        'interventions' => ['sapeur_id'],
        'inventaire_lignes' => ['verifie_par_sapeur_id', 'traite_par_sapeur_id'],
        'inventaires' => ['demarre_par_sapeur_id', 'valide_par_sapeur_id'],
        'jalons' => ['sapeur_id'],
        'missions' => ['sapeur_id'],
        'mutations' => ['sapeur_id'],
        'paiements' => ['sapeur_id'],
        'permis' => ['sapeur_id'],
        'quittances' => ['sapeur_id'],
        'reference_rtas' => ['sapeur_id'],
        'sapeur_telephone' => ['sapeur_id'],
        'sis_params' => ['sapeur_id'],
        'sms_numeros' => ['sapeur_id'],
        'travaux' => ['sapeur_id', 'auteur_id'],
    ];

    /**
     * Tables où une fiche n'a qu'une ligne par élément (colonne associée), ou une
     * seule ligne (null) : en cas de doublon, la ligne du sapeur est gardée.
     */
    private const UNIQUES = [
        'exercice_sapeur' => 'exercice_id',
        'quittances' => 'intervention_id',
        'grade_sapeur' => 'grade_id',
        'groupe_sapeur' => 'groupe_id',
        'permis' => 'permis_type_id',
        'reference_rtas' => null,
        'ics_tokens' => null,
    ];

    /**
     * Données personnelles reprises du civil si vides chez le sapeur (la date de
     * naissance reste celle du sapeur : celle du civil est fictive)
     */
    private const CHAMPS = [
        'suffixe', 'rue', 'no_rue', 'no_avs', 'profession', 'employeur', 'lieu_de_travail',
        'email', 'iban', 'remarque', 'localite_id', 'civilite_id',
    ];

    public function handle(): int
    {
        $sis = (string) $this->option('sis');
        if (!Sis::isValid($sis)) {
            $this->error("SIS inconnu : {$sis}");

            return self::FAILURE;
        }
        Sis::use($sis);

        $dryRun = (bool) $this->option('dry-run');
        $sapeurId = (int) $this->argument('sapeur_id');
        $civilId = (int) $this->argument('civil_id');

        DB::beginTransaction();
        try {
            $this->fusionner($sapeurId, $civilId);
        } catch (Throwable $e) {
            DB::rollBack();
            if (!$e instanceof ArrayException) {
                throw $e;
            }
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        $dryRun ? DB::rollBack() : DB::commit();

        $this->info(($dryRun ? '[dry-run] ' : '') . "Fiche {$civilId} fusionnée dans la fiche {$sapeurId} (SIS {$sis})");
        $this->warn("Pensez à vérifier dans GestSIS_Auth le compte éventuellement lié à la fiche {$civilId}.");

        return self::SUCCESS;
    }

    /**
     * @throws ArrayException
     */
    private function fusionner(int $sapeurId, int $civilId): void
    {
        if ($sapeurId === $civilId) {
            throw new NotReportableException([], "Les deux fiches doivent être différentes.");
        }
        $sapeur = Sapeur::find($sapeurId);
        $civil = Sapeur::find($civilId);
        if ($sapeur === null || $civil === null) {
            throw new NotReportableException([], "Fiche introuvable.");
        }
        if ($sapeur->statut_sapeur === SapeurBusiness::STATUT_JAMAIS) {
            throw new NotReportableException([], "La fiche {$sapeurId} n'a jamais été sapeur.");
        }
        if ($civil->statut_civil === SapeurBusiness::STATUT_JAMAIS || $civil->statut_sapeur !== SapeurBusiness::STATUT_JAMAIS) {
            throw new NotReportableException([], "La fiche {$civilId} n'est pas un civil seul.");
        }

        // Les périodes civiles des deux fiches ne doivent pas se chevaucher
        $periodesCiviles = Mutation::where('sapeur_id', $sapeurId)->where('nature', SapeurBusiness::NATURE_CIVIL)->get();
        foreach (Mutation::where('sapeur_id', $civilId)->get() as $mutation) {
            SapeurBusiness::verifyMutationPeriode($mutation->toArray(), $periodesCiviles);
            $periodesCiviles->push($mutation);
        }

        $doublons = [];
        foreach (self::UNIQUES as $table => $element) {
            $civilRows = DB::table($table)->where('sapeur_id', $civilId);
            if ($element !== null) {
                $civilRows->whereIn($element, DB::table($table)->where('sapeur_id', $sapeurId)->pluck($element));
            } elseif (!DB::table($table)->where('sapeur_id', $sapeurId)->exists()) {
                continue;
            }
            $doublons[] = [$table, $civilRows->delete()];
        }

        $lignes = [];
        foreach (self::COLONNES as $table => $colonnes) {
            foreach ($colonnes as $colonne) {
                $lignes[] = ["$table.$colonne", DB::table($table)->where($colonne, $civilId)->update([$colonne => $sapeurId])];
            }
        }

        $differences = [];
        foreach (self::CHAMPS as $champ) {
            $valeurCivil = $civil->$champ;
            if ($valeurCivil === null || $valeurCivil === '') {
                continue;
            }
            if ($sapeur->$champ === null || $sapeur->$champ === '') {
                $sapeur->$champ = $valeurCivil;
                if ($champ === 'iban') {
                    $sapeur->iban_statut = $civil->iban_statut;
                }
            } elseif ($sapeur->$champ != $valeurCivil) {
                $differences[] = [$champ, $sapeur->$champ, $valeurCivil];
            }
        }

        $civil->delete();
        SapeurBusiness::majStatuts($sapeur, Mutation::where('sapeur_id', $sapeurId)->get());
        SapeurBusiness::updateFonctionPrincipale($sapeurId);
        SapeurBusiness::updateMainGrade($sapeurId);

        $this->table(['Colonne', 'Lignes rattachées'], array_filter($lignes, fn($ligne) => $ligne[1] > 0));
        $doublons = array_filter($doublons, fn($ligne) => $ligne[1] > 0);
        if ($doublons !== []) {
            $this->table(['Table', 'Doublons du civil supprimés'], $doublons);
        }
        if ($differences !== []) {
            $this->warn('Champs différents, valeur du sapeur conservée :');
            $this->table(['Champ', 'Sapeur', 'Civil'], $differences);
        }
    }
}
