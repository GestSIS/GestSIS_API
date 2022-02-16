<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIndemniteInterventionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('indemnite_intervention_types', function (Blueprint $table) {
            $table->renameColumn('solde', 'tarif'); // Renommé
            $table->renameColumn('solde_min', 'tarif_min'); // Renommé
            $table->renameColumn('solde_min_pour', 'tarif_min_pour'); // Renommé

            $table->unsignedInteger('type'); // Renommé
        });
        Schema::table('indemnite_intervention_fonctions', function (Blueprint $table) {
            $table->renameColumn('solde', 'tarif'); // 

            $table->unsignedBigInteger('fonction_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('indemnite_intervention_types', function (Blueprint $table) {
            $table->renameColumn('tarif', 'solde'); // Renommé
            $table->renameColumn('tarif_min', 'solde_min'); // Renommé
            $table->renameColumn('tarif_min_pour', 'solde_min_pour'); // Renommé
        });
        Schema::table('indemnite_intervention_fonctions', function (Blueprint $table) {
            $table->renameColumn('tarif', 'solde'); // Renommé
        });
    }
}
