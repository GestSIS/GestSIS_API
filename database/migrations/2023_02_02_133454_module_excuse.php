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
        Schema::table('exercice_sapeur', function (Blueprint $table) {
            $table->smallInteger('absent')->default(0);

            // -1 -> Refusée
            // 0 -> A traiter
            // 1 -> Acceptée
            $table->smallInteger('excuse_statut')->default(0);

            // Saisie par le sapeur
            // excuse_type_id -> déjà ok
            $table->date('date_demande')->nullable()->default(null);
            $table->string('remarque')->default('');
            $table->string('justificatif_path')->default('');
            $table->string('justificatif_filename')->default('');

            // Saisie lors de la validation
            $table->date('date_validation')->nullable()->default(null);
            $table->string('justification')->default('');
        });

        Schema::create('excuse_params', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->boolean('actif'); // Nb jours pour s'excuser
            $table->integer('delai_excuse'); // Nb jours pour s'excuser
            $table->boolean('email_rappel');
            $table->string('texte_email_rappel')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exercice_sapeur', function (Blueprint $table) {
            $table->dropColumn('absent');
            $table->dropColumn('excuse_statut');

            $table->dropColumn('date_demande');
            $table->dropColumn('remarque');
            $table->dropColumn('justificatif_path');
            $table->dropColumn('justificatif_filename');

            $table->dropColumn('date_validation');
            $table->dropColumn('justification');
        });

        Schema::drop('excuse_params');
    }
};
