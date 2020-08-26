<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmendesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amendes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('motif');
            $table->decimal('montant');
            $table->date('date');

            // Foreign keys
            $table->unsignedBigInteger('sapeur_id');
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('exercice_id');
            $table->foreign('exercice_id')->references('id')->on('exercices');

            $table->unsignedBigInteger('exercice_comptable_id');
            $table->foreign('exercice_comptable_id')->references('id')->on('exercice_comptables');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amendes');
    }
}
