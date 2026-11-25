<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mutations civiles.
 *
 * Une personne peut être sapeur, civil, ou les deux (en même temps ou l'un après
 * l'autre, typiquement un ancien sapeur qui reste civil après sa fin de service).
 * Les périodes civiles sont des mutations de `nature` civile. Le champ
 * `sapeurs.type` (sapeur / civil / recrue, exclusif) et le champ `sapeurs.actif`
 * sont remplacés par deux statuts recalculés depuis les mutations : `statut_sapeur`
 * et `statut_civil` (0 = jamais, 1 = actif, 2 = inactif). Une recrue n'a aucune
 * mutation.
 *
 * Reprise des données :
 * - chaque civil reçoit une mutation civile depuis sa date de création, close à sa
 *   dernière modification s'il est inactif (approximation acceptée) ;
 * - un sapeur sans aucune mutation (données historiques) en reçoit une, pour ne pas
 *   repasser en recrue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            // 0 = sapeur, 1 = civil
            $table->smallInteger('nature')->default(0)->after('sapeur_id');
        });

        Schema::table('sapeurs', function (Blueprint $table) {
            // 0 = jamais, 1 = actif, 2 = inactif
            $table->smallInteger('statut_sapeur')->default(0)->after('type');
            $table->smallInteger('statut_civil')->default(0)->after('statut_sapeur');
        });

        // Fin des inactifs : dernière modification, au plus tard hier (sinon encore actifs
        // aujourd'hui) et jamais avant le début
        $sortieInactif = fn(string $debut) => "GREATEST($debut, LEAST(DATE(COALESCE(updated_at, NOW())), CURDATE() - INTERVAL 1 DAY))";

        $debutCivil = "DATE(COALESCE(created_at, NOW()))";
        DB::statement("
            INSERT INTO mutations (sapeur_id, nature, incorporation, sortie, motif, localite_id, created_at, updated_at)
            SELECT id, 1, $debutCivil, IF(actif = 1, NULL, {$sortieInactif($debutCivil)}), '', localite_id, NOW(), NOW()
            FROM sapeurs
            WHERE type = 1
        ");

        $debutSapeur = "IF(annee_incorporation REGEXP '^[0-9]{4}$', CONCAT(annee_incorporation, '-01-01'), DATE(COALESCE(created_at, NOW())))";
        DB::statement("
            INSERT INTO mutations (sapeur_id, nature, incorporation, sortie, motif, localite_id, created_at, updated_at)
            SELECT id, 0, $debutSapeur, IF(actif = 1, NULL, {$sortieInactif($debutSapeur)}), '', localite_id, NOW(), NOW()
            FROM sapeurs s
            WHERE type = 0 AND NOT EXISTS (SELECT 1 FROM mutations m WHERE m.sapeur_id = s.id)
        ");

        // Même règle que SapeurBusiness::statuts() : 3 mois d'anticipation pour une mutation sapeur
        DB::statement("
            UPDATE sapeurs s SET
                statut_sapeur = CASE
                    WHEN EXISTS (
                        SELECT 1 FROM mutations m WHERE m.sapeur_id = s.id AND m.nature = 0
                            AND m.incorporation - INTERVAL 3 MONTH <= CURDATE() AND (m.sortie IS NULL OR m.sortie >= CURDATE())
                    ) THEN 1
                    WHEN EXISTS (SELECT 1 FROM mutations m WHERE m.sapeur_id = s.id AND m.nature = 0) THEN 2
                    ELSE 0
                END,
                statut_civil = CASE
                    WHEN EXISTS (
                        SELECT 1 FROM mutations m WHERE m.sapeur_id = s.id AND m.nature = 1
                            AND m.incorporation <= CURDATE() AND (m.sortie IS NULL OR m.sortie >= CURDATE())
                    ) THEN 1
                    WHEN EXISTS (SELECT 1 FROM mutations m WHERE m.sapeur_id = s.id AND m.nature = 1) THEN 2
                    ELSE 0
                END
        ");

        Schema::table('sapeurs', function (Blueprint $table) {
            $table->dropColumn(['type', 'actif']);
        });
    }

    public function down(): void
    {
        Schema::table('sapeurs', function (Blueprint $table) {
            $table->smallInteger('type')->default(0)->after('statut_civil');
            $table->integer('actif')->default(0)->after('type');
        });
        DB::statement("UPDATE sapeurs SET actif = (statut_sapeur = 1 OR statut_civil = 1)");

        // Type principal : sapeur actif > civil actif > ancien sapeur > ancien civil, recrue sinon
        DB::statement("
            UPDATE sapeurs SET type = CASE
                WHEN statut_sapeur = 1 THEN 0
                WHEN statut_civil = 1 THEN 1
                WHEN statut_sapeur = 2 THEN 0
                WHEN statut_civil = 2 THEN 1
                ELSE 2
            END
        ");
        DB::statement("DELETE FROM mutations WHERE nature = 1");

        Schema::table('sapeurs', function (Blueprint $table) {
            $table->dropColumn(['statut_sapeur', 'statut_civil']);
        });
        Schema::table('mutations', function (Blueprint $table) {
            $table->dropColumn('nature');
        });
    }
};
