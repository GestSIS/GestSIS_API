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
        Schema::create('indemnite_intervention_fonctions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->decimal('solde');

            $table->unsignedBigInteger('fonction_id');
            $table->foreign('fonction_id')->references('id')->on('fonctions')->onDelete('cascade');

            $table->unsignedBigInteger('indemnite_int_id');
            $table->foreign('indemnite_int_id')->references('id')->on('indemnite_intervention_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('indemnite_intervention_fonctions');
    }
};
