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
        Schema::create('sis_params', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom');
            $table->string('rue');
            $table->string('numero');
            $table->string('district');
            $table->string('no_arrondissement');
            $table->string('telephone');
            $table->string('email');
            $table->string('iban');
            $table->string('bic');

            // Localité
            $table->unsignedBigInteger('localite_id');
            $table->foreign('localite_id')->references('id')->on('localites');

            // Commandant
            $table->unsignedBigInteger('sapeur_id');
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sis_params');
    }
};
