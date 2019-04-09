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
            $tabel->string('rue');
            $tabel->string('no_rue');
            $tabel->date('date_naissance');
            // $tabel->date('date_inco'); // TODO: vérifier si nécessaire
            // $tabel->date('date_sortie'); //TODO: vérifier si nécessaire
            $tabel->string('no_avs');

            $table->string('profession');
            $tabel->string('employeur');
            $tabel->string('lieu_de_travail');
            
            // $tabel->integer('Nip'); // TODO: vérifier a quoi ça sert
            
            $tabel->string('tel_portable');
            $tabel->string('tel_prive');
            $tabel->string('tel_professionnel');
            $tabel->string('email');
            $tabel->integer('actif');
            
            $tabel->integer('tel_portable_rta');
            $tabel->integer('tel_prive_rta');
            $tabel->integer('tel_proffesionnel_rta');

            $tabel->integer('tel_portable_prio');
            $tabel->integer('tel_prive_prio');
            $tabel->integer('tel_proffesionnel_prio');

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
            $table->bigInteger('localite_id')->unsigned();
            $table->foreign('localite_id')->references('id')->on('localite');
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
