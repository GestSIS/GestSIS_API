<?php

use App\Infrastructure\Models\Ecriture;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecritures', function (Blueprint $table) {
            $table->unsignedInteger('module')->default(0);
            // 0. Divers
            // 1. Exercice
            // 2. Intervention
            // 3. Frais Annuel
            // 4. Indemnité Annuel
            // 5. AVS
            // 6. Amende
            // 7. Décompte d'heures
            // 8. Cours
            // 9. Remboursement à l'employeur ?
            $table->unsignedInteger('type')->default(0);
            // 0. 'Autre',
            // 1. 'Solde',
            // 2. 'Indemnité',
            // 3. 'Frais forfaitaire',
            // 4. 'Frais effectif',
            // 5. 'Charges AVS/AC'
        });

        Ecriture::whereNotNull('exercice_id')->update(['module' => 1]);
        Ecriture::whereNotNull('intervention_id')->update(['module' => 2]);
        // Ecriture::whereNotNull('frais_annuel')->update(['module' => 3]);
        // Ecriture::whereNotNull('indemnite_annuel')->update(['module' => 4]);
        // Ecriture::whereNotNull('avs')->update(['module' => 5]);
        // Ecriture::whereNotNull('amende')->update(['module' => 6]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecritures', function (Blueprint $table) {
            $table->dropColumn('module');
        });
    }
};
