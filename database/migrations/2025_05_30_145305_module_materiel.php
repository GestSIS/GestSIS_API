<?php

use App\Infrastructure\Models\Article;
use App\Infrastructure\Models\Couleur;
use App\Infrastructure\Models\Emplacement;
use App\Infrastructure\Models\Lavage;
use App\Infrastructure\Models\MaterielCategorie;
use App\Infrastructure\Models\MaterielEvent;
use App\Infrastructure\Models\MaterielPersonnel;
use App\Infrastructure\Models\MaterielType;
use App\Infrastructure\Models\Vehicule;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('couleurs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom')->unique();
            $table->string('texte', length: 9)->comment('hex code pour la couleur du texte');
            $table->string('fond', length: 9)->comment('hex code pour l\'arrière plan');
        });

        // Déjà existante
        Schema::table('materiel_categories', function (Blueprint $table) {
            $table->integer('tri')->unique();

            $table->unsignedBigInteger('couleur_id');
            $table->foreign('couleur_id')->references('id')->on('couleurs');
        });

        Schema::create('emplacements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->integer('tri');
            $table->string('remarque')->default('');
            $table->boolean('est_etiquete')->default(false)->comment('Est-ce que les articles dans cet inventaire portent une étiquette');
            $table->date('impression_inventaire')->nullable()->default(null);  // TODO: Quoi faire avec ?

            $table->foreignId('couleur_id')->constrained();

            $table->unsignedBigInteger('parent_id')->nullable()->default(null);
            $table->foreign('parent_id')->references('id')->on('emplacements');

            $table->boolean('statut')->default(true);
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('numero')->default('');
            $table->string('uuid')->unique();
            $table->string('achat')->default('');
            $table->string('taille')->default('');
            $table->boolean('est_etiquete')->default(false)->comment('Est étiqueté');
            $table->boolean('est_unique')->default(false);
            $table->string('remarque')->default('');

            $table->date('attribution')->nullable()->default(null);
            $table->date('retour')->nullable()->default(null);

            $table->foreignId('materiel_type_id')->constrained();
            $table->foreignId('sapeur_id')->nullable()->constrained()->default(null);
            $table->foreignId('emplacement_id')->nullable()->constrained()->default(null);
            $table->string('compartiment')->default('');

            // Spécifique pour véhicule
            $table->string('designation')->default('');
            $table->string('immatriculation')->default('');
            $table->string('chassis')->default('');

            $table->boolean('statut')->default(true);
        });

        Schema::create('hangars', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('designation');
            $table->string('rue')->default('');
            $table->string('no_rue')->default('');
            $table->boolean('statut')->default(true);

            $table->foreignId('localite_id')->constrained();
        });

        Schema::table('materiel_types', function (Blueprint $table) {

            // Champs déjà existants app/Infrastructure/Models/Vehicule.php
            // $table->bigIncrements('id');
            // $table->timestamps();

            // $table->string('designation');
            // $table->boolean('taille')->default(true); // TODO:: supprimer cet ancien champ
            // $table->foreignId('materiel_categorie_id')->constrained();

            $table->integer('type')->default(0)->remarque("Permet de lier le type à un sous-type tel que tuyau, vehicule, batterie, ...");

            $table->string('prix')->default('');
            $table->string('fournisseur')->default('');
            $table->string('reparateur')->default('');
            $table->boolean('a_controller')->default(false)->comment('Besoin de contrôller des aspects de ce matériel');
            $table->string('remarque')->default('');
            $table->integer('tri');

            $table->string('prefix')->default('');
            $table->boolean('est_numerote')->default(false);
            $table->boolean('est_attribuable')->default(false)->comment('Peut être distribué à un sapeur');
            $table->boolean('est_taillee')->default(false)->comment('Possède une taille');
            $table->boolean('est_lavable')->default(false)->comment('Pour activer le suivi des lavages');

            $table->foreignId('fonction_id')->nullable()->comment('fonction responsable de l \'entretient')->constrained();
        });

        Schema::create('batterie_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom')->unique();
        });

        Schema::create('materiel_type_batteries', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->foreign('id')->references('id')->on('materiel_types')->onDelete('cascade');
            $table->timestamps();

            $table->integer('nombre');
            $table->foreignId('batterie_type_id')->constrained();
        });

        Schema::create('tuyau_diametres', function (Blueprint $table) {
            $table->timestamps();

            $table->bigIncrements('id');
            $table->integer('diametre')->comment('diamètre du tuyau en mm')->unique();
        });

        Schema::create('materiel_type_tuyaux', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->foreign('id')->references('id')->on('materiel_types')->onDelete('cascade');
            $table->timestamps();

            $table->integer('longeur')->comment('longeur du tuyau en metre');
            $table->boolean('separement')->comment('Est-ce que le tuyau est roule separement ou en dévidoir ?');

            $table->foreignId('tuyau_diametre_id')->constrained();
        });

        Schema::create('lavages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->foreignId('article_id')->constrained();
            $table->date('date');
        });

        Schema::create('maintenance_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->integer('periodicite'); // Si à zéro alors peut-être fair sur un ou plusieurs matériels
            $table->integer('nb_max');
            $table->boolean('externalise');
        });

        Schema::create('maintenance_type_pour', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->foreignId('maintenance_type_id')->constrained();
            $table->foreignId('materiel_type_id')->constrained();

            $table->unique(['maintenance_type_id', 'materiel_type_id'], 'maintenance_type_pour_unique');
        });

        Schema::create('maintenances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->date('date');
            $table->string('remarque')->default('');
            $table->string('responsable');

            // user_id, laisser tomber pour le moment

            $table->foreignId('maintenance_type_id')->constrained();
        });

        Schema::create('maintenance_articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->boolean('effectuee');
            $table->boolean('reussie');
            $table->string('remarque')->default('');

            $table->foreignId('maintenance_id')->constrained();
            $table->foreignId('article_id')->constrained();

            $table->unique(['maintenance_id', 'article_id']);
        });

        Schema::create('inventaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->date('date');
            $table->string('designation');
            $table->string('remarque')->default('');
            $table->string('responsable');

            // user_id, laisser tomber pour le moment

            $table->foreignId('emplacement_id')->constrained();
        });

        Schema::create('inventaire_articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->boolean('present');

            $table->foreignId('inventaire_id')->constrained();
            $table->foreignId('article_id')->constrained();

            $table->unique(['article_id', 'inventaire_id']);
        });

        Schema::table('intervention_vehicule', function (Blueprint $table) {
            $table->dropForeign(['vehicule_id']);
            $table->foreign('vehicule_id')->references('id')->on('articles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batterie_types');
        Schema::dropIfExists('couleurs');
        Schema::dropIfExists('emplacements');
        Schema::dropIfExists('hangars');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('tuyau_diametres');
        Schema::dropIfExists('materiel_type_batteries');
        Schema::dropIfExists('materiel_type_tuyaux');
        Schema::dropIfExists('maintenance_types');
        Schema::dropIfExists('maintenance_type_pour');
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('maintenance_articles');
        Schema::dropIfExists('inventaires');
        Schema::dropIfExists('inventaire_articles');
    }
};
