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
        Schema::create('batterie_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('nom')->unique();
        });

        Schema::create('couleurs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom')->unique();
            $table->string('texte', length: 9)->comment('hex code pour la couleur du texte');
            $table->string('fond', length: 9)->comment('hex code pour l\'arrière plan');
        });

        // TODO: Create a basic couleur

        // Déjà existante
        Schema::table('materiel_categories', function (Blueprint $table) {
            $table->integer('tri')->default(1);

            $table->unsignedBigInteger('couleur_id')->default(1);
            $table->foreign('couleur_id')->references('id')->on('couleurs');
        });

        // TODO: générer une valeur pour tri des categories

        Schema::table('materiel_categories', function (Blueprint $table) {
            $table->integer('tri')->unique()->change();

            $table->unsignedBigInteger('couleur_id')->update();
            $table->foreign('couleur_id')->references('id')->on('couleurs');
        });

        Schema::create('inventaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->date('date');
            $table->string('remarque');

            $table->unsignedBigInteger('emplacement_id');
            $table->foreign('emplacement_id')->references('id')->on('emplacements');

            $table->unsignedBigInteger('sapeur_id');
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            // TODO: remplacer par sapeur_id
            $table->string('personne');
        });

        Schema::create('emplacements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom');
            $table->string('remarque');
            $table->integer('tri');
            $table->boolean('est_etiquete')->comment('Est-ce que les articles dans cet inventaire portent une étiquette');
            $table->date('impression_inventaire')->nullable()->default(null);

            $table->unsignedBigInteger('couleur_id');
            $table->foreign('couleur_id')->references('id')->on('couleurs');

            $table->unsignedBigInteger('parent_id');
            $table->foreign('parent_id')->references('id')->on('emplacements');

            $table->boolean('statut')->default(true);

            // TODO: Contrainte unicité sur article_id et inventaire_id
        });

        Schema::table('vehicules', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->change();

            // TODO: Supprimer designation ??? Non
            // Ou bien mettre un flag dans emplacement ???
            // Que faire en cas de suppression du véhicule ?
            // Le conserver dans le système mais plus dans les emplacements ?
            // Ajouter un field statut dans emplacement pour pouvoir désactiver
            // ceux inactif mais quand même à garder

            // TODO: Supprimer champs designation, statut et tri
            // $table->string('designation');
            // $table->boolean('statut');
            // $table->integer('tri');
            // $table->decimal('forfait', 5, 2);
            // $table->decimal('unite', 5, 2);

            // TODO: Numéro chassi
            // TODO: Autres
        });

        Schema::table('hangars', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->change();

            // TODO: Supprimer designation ???
            // Ou bien mettre un flag dans emplacement ???

            $table->string('rue');
            $table->string('no_rue');

            $table->bigInteger('localite_id')->unsigned();
            $table->foreign('localite_id')->references('id')->on('localites');
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('numero');
            $table->boolean('est_etiquete');
            $table->boolean('est_unique');
            $table->string('remarque');
            $table->string('compartiment');

            // TODO: Created and Deleted fields

            $table->unsignedBigInteger('emplacement_id')->nullable();
            $table->foreign('emplacement_id')->references('id')->on('emplacements');

            $table->unsignedBigInteger('sapeur_id')->nullable();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('materiel_type_id');
            $table->foreign('materiel_type_id')->references('id')->on('materiel_types');
        });

        Schema::update('materiel_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            // $table->string('designation');
            $table->string('prix');
            $table->string('fournisseur');
            $table->string('reparateur');
            $table->string('compartiment');
            $table->boolean('a_controller')->comment('Besoin de contrôller des aspects de ce matériel');
            $table->string('prefix')->comment();
            $table->string('remarque');
            $table->int('tri');

            $table->boolean('est_attribuable');

            // TODO: Created and Deleted fields

            $table->unsignedBigInteger('materiel_categorie_id');
            $table->foreign('materiel_categorie_id')->references('id')->on('materiel_categories');

            $table->unsignedBigInteger('fonction_id')->nullable()->comment('fonction responsable de l \'entretient');
            $table->foreign('fonction_id')->references('id')->on('fonctions');
        });

        Schema::create('tuyau_diametres', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('diametre');

            $table->unsignedBigInteger('batterie_type_id');
            $table->foreign('batterie_type_id')->references('id')->on('batterie_types');
        });

        Schema::create('produit_batteries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->integer('nombre');

            $table->unsignedBigInteger('batterie_type_id');
            $table->foreign('batterie_type_id')->references('id')->on('batterie_types');
        });

        Schema::create('produit_tuyaus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->integer('longeur')->comment('longeur du tuyau en metre');
            $table->boolean('separement')->comment('Est-ce que le tuyau est roule separement ?');

            $table->unsignedBigInteger('tuyau_diametre_id');
            $table->foreign('tuyau_diametre_id')->references('id')->on('tuyau_diametres');
        });

        // Schema::create('inventaire_articles', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->timestamps();

        //     $table->boolean('present');

        //     $table->unsignedBigInteger('article_id');
        //     $table->foreign('article_id')->references('id')->on('articles');

        //     $table->unsignedBigInteger('inventaire_id');
        //     $table->foreign('inventaire_id')->references('id')->on('inventaires');

        //     // TODO: Contrainte unicité sur article_id et inventaire_id
        // });

        // Schema::create('maintenances', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->timestamps();

        //     $table->string('nom');
        //     $table->int('periodicite');
        //     $table->boolean('externalise');

        //     $table->unsignedBigInteger('materiel_type_id');
        //     $table->foreign('materiel_type_id')->references('id')->on('materiel_types');
        // });

        // Schema::create('maintenance_execs', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->timestamps();

        //     $table->string('nom');
        //     $table->string('remarque');
        //     $table->string('responsable');
        //     $table->date('date');
        //     $table->boolean('externalise');

        //     // TODO: user_id

        //     $table->unsignedBigInteger('maintenance_id');
        //     $table->foreign('maintenance_id')->references('id')->on('maintenances');
        // });

        // Schema::create('maintenance_exec_lignes', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->timestamps();

        //     $table->string('nom');
        //     $table->boolean('effectuee');
        //     $table->boolean('reussie');
        //     $table->string('remarque');

        //     // TODO: user_id

        //     $table->unsignedBigInteger('maintenance_exec_id');
        //     $table->foreign('maintenance_exec_id')->references('id')->on('maintenance_execs');

        //     $table->unsignedBigInteger('article_id');
        //     $table->foreign('article_id')->references('id')->on('articles');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absences');
        Schema::dropIfExists('absence_params');
    }
};
