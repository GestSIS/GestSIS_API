<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeureExercicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heure_exercices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedDecimal('quantite');

            //TODO:
            $table->unsignedInteger('type');

            $table->string('designation');
            $table->unsignedDecimal('montant');

            $table->unsignedBigInteger('exercice_id');
            $table->foreign('exercice_id')->references('id')->on('exercices');

            $table->unsignedBigInteger('sapeur_id');
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('type_unite_id');
            $table->foreign('type_unite_id')->references('id')->on('type_unites');

            $table->unsignedBigInteger('heure_exercice_type_id')->nullable();
            $table->foreign('heure_exercice_type_id')->references('id')->on('heure_exercice_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heure_exercices');
    }
}
