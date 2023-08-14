<?php

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
        Schema::table('travaux', function (Blueprint $table) {
            $table->unsignedBigInteger('exercice_id')->nullable();
            $table->foreign('exercice_id')->references('id')->on('exercices');

            $table->unsignedBigInteger('intervention_id')->nullable();
            $table->foreign('intervention_id')->references('id')->on('interventions');
        });

        // Value
        // 0 -> non-dispo
        // 1 -> obligatoire (si aussi configuré pour inter, alors soit l'un ou l'autre de requis)
        // 2 -> optionnel
        Schema::table('travail_types', function (Blueprint $table) {
            $table->smallInteger('dispo_pour_exercice')->default(0);
            $table->smallInteger('dispo_pour_intervention')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('travaux', function (Blueprint $table) {
            $table->dropColumn('exercice_id');
            $table->dropColumn('intervention_id');
        });

        Schema::table('travail_types', function (Blueprint $table) {
            $table->dropColumn('dispo_pour_exercice');
            $table->dropColumn('dispo_pour_intervention');
        });
    }
};
