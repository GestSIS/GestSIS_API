<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Échéance du prochain contrôle, enregistrée sur chaque exécution d'un
 * contrôle PERIODIQUE : par défaut date de l'exécution + récurrence du
 * contrôle, mais modifiable à la saisie (ex : un service véhicule dont la
 * périodicité varie selon l'âge du véhicule). Reste nul pour un contrôle
 * NON_PERIODIQUE, qui ne suit pas d'échéance par date.
 *
 * Remplace le calcul à la volée (dernière exécution + récurrence du contrôle)
 * par cette valeur stockée, ce qui centralise le calcul du statut « à jour /
 * en retard » d'un article sur une seule source au lieu de le refaire à
 * plusieurs endroits.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controle_execs', function (Blueprint $table) {
            $table->date('date_echeance')->nullable()->after('executed_at')->comment('Échéance du prochain contrôle, uniquement pour un contrôle PERIODIQUE');
        });
    }

    public function down(): void
    {
        Schema::table('controle_execs', function (Blueprint $table) {
            $table->dropColumn('date_echeance');
        });
    }
};
