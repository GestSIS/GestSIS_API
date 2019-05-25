<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSapeursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sapeurs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom');
            $table->string('prenom');
            $table->string('suffixe', 50);
            $table->string('rue');
            $table->string('no_rue');
            $table->date('date_naissance');
            // $table->date('date_inco'); // TODO: vérifier si nécessaire
            // $table->date('date_sortie'); //TODO: vérifier si nécessaire
            $table->string('no_avs');

            $table->string('profession');
            $table->string('employeur');
            $table->string('lieu_de_travail');
            
            // $table->integer('Nip'); // TODO: vérifier a quoi ça sert

            $table->string('email');
            $table->integer('actif');

            //TODO: Add telephone table

            //Banque
            // CptBan
            // NoCPP
            // Iban
            // Iban Status
            $table->string('iban');
            $table->integer('iban_status');
            
            $table->text('remarque');
            $table->integer('porteur');

            // Foreign keys
            $table->unsignedBigInteger('localite_id');
            $table->foreign('localite_id')->references('id')->on('localites');

            $table->unsignedBigInteger('civilite_id');
            $table->foreign('civilite_id')->references('id')->on('civilites');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sapeurs');
    }
}
