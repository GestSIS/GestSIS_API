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
        Schema::create('indemnite_intervention_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');

            $table->decimal('solde'); // Renommé
            $table->decimal('solde_min')->nullable(); // Renommé
            $table->decimal('solde_min_pour')->nullable(); // Renommé

            $table->decimal('taux_weekend')->nullable();
            $table->decimal('taux_nuit')->nullable();
            $table->time('debut')->nullable();
            $table->time('fin')->nullable();

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');

            $table->unsignedBigInteger('phase_id')->nullable();
            $table->foreign('phase_id')->references('id')->on('phase_types');

            $table->unsignedBigInteger('type_unite_id');
            $table->foreign('type_unite_id')->references('id')->on('type_unites');

            $table->unsignedBigInteger('ecriture_categorie_id');
            $table->foreign('ecriture_categorie_id')->references('id')->on('ecriture_categories');

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
};
