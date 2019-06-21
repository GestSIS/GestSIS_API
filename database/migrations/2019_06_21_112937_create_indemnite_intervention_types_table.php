<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndemniteInterventionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indemnite_intervention_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');

            $table->unsignedDecimal('solde');
            $table->unsignedDecimal('solde_min')->nullable();
            $table->unsignedDecimal('solde_min_pour')->nullable();
            $table->unsignedInteger('solde_min_phase')->nullable();

            $table->unsignedDecimal('taux_weekend');
            $table->unsignedDecimal('taux_nuit');
            $table->time('debut');
            $table->time('fin');

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');

            $table->unsignedBigInteger('type_unite_id');
            $table->foreign('type_unite_id')->references('id')->on('type_unites');

            $table->boolean('par_fonction');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('indemnite_intervention_types');
    }
}
