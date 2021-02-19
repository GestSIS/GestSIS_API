<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndemniteExerciceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indemnite_exercice_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');

            $table->unsignedDecimal('solde');
            $table->unsignedDecimal('indemnite');

            // $table->unsignedDecimal('solde_heure_additionelle')->default(0.0);
            // $table->unsignedDecimal('indemnite_heure_additionelle')->default(0.0);

            $table->unsignedDecimal('solde_min')->nullable();
            $table->unsignedDecimal('solde_min_pour')->nullable();

            $table->unsignedBigInteger('type_unite_id');
            $table->foreign('type_unite_id')->references('id')->on('type_unites');

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');

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
        Schema::dropIfExists('indemnite_exercice_types');
    }
}
