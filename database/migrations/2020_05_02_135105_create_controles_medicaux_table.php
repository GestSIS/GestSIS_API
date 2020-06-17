<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateControlesMedicauxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('controles_medicaux', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->date('consultation');
            $table->date('validite');

            $table->boolean('accepter');
            $table->boolean('en_cours');

            // Foreign keys
            $table->unsignedBigInteger('sapeur_id');
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('medecin_id');
            $table->foreign('medecin_id')->references('id')->on('medecins');

            $table->unsignedBigInteger('controle_medical_type_id');
            $table->foreign('controle_medical_type_id')->references('id')->on('controle_medical_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('controles_medicaux');
    }
}
