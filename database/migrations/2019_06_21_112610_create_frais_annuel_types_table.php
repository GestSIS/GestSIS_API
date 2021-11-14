<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFraisAnnuelTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frais_annuel_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->boolean('cumulable')->default(False);

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');

            $table->unsignedBigInteger('ecriture_categorie_id');
            $table->foreign('ecriture_categorie_id')->references('id')->on('ecriture_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frais_annuel_types');
    }
}
