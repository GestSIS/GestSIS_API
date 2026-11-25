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
        Schema::table('sapeurs', function (Blueprint $table) {
            $table->dropColumn(['statut_sapeur', 'statut_civil']);
        });
        Schema::table('mutations', function (Blueprint $table) {
            $table->dropColumn('nature');
        });
    }
};
