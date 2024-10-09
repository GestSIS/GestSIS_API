<?php

use App\Infrastructure\Models\Article;
use App\Infrastructure\Models\Couleur;
use App\Infrastructure\Models\Emplacement;
use App\Infrastructure\Models\MaterielCategorie;
use App\Infrastructure\Models\MaterielPersonnel;
use App\Infrastructure\Models\Vehicule;
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

        // Create a basic couleur
        $couleur = couleur::create(['nom' => 'default', 'texte' => '#ffffff00', 'fond' => '#fc031780']);

        // Déjà existante
        Schema::table('materiel_categories', function (Blueprint $table) {
            $table->integer('tri')->default(1);

            $table->unsignedBigInteger('couleur_id')->default(0);
            $table->foreign('couleur_id')->references('id')->on('couleurs');
        });

        // générer une valeur pour tri des categories
        $categories = MaterielCategorie::all();
        foreach ($categories as $categorie) {
            $categorie->tri = $categorie->id;
            $categorie->save();
        }

        Schema::table('materiel_categories', function (Blueprint $table) {
            $table->integer('tri')->unique()->change();

            $table->unsignedBigInteger('couleur_id')->change();
        });

        Schema::create('inventaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->date('date');
            $table->string('remarque');

            $table->foreignId('emplacement_id')->constrained();

            $table->foreignId('sapeur_id')->constrained();

            // TODO: remplacer par sapeur_id
            $table->string('personne');
        });

        Schema::create('emplacements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->string('remarque');
            $table->boolean('est_etiquete')->default(false)->comment('Est-ce que les articles dans cet inventaire portent une étiquette');
            $table->date('impression_inventaire')->nullable()->default(null);

            $table->foreignId('couleur_id')->constrained();

            $table->unsignedBigInteger('parent_id');
            $table->foreignId('parent_id')->references('id')->on('emplacements');

            $table->boolean('statut')->default(true);
        });


        // Créer un emplacement pour chaque véhicule
        $vehicules = Vehicule::all();
        foreach ($vehicules as $vehicule) {
            $emplacement = new Emplacement();
            $emplacement->designation = $vehicule->designation;
            $emplacement->remarque = '';
            $emplacement->tri = $vehicule->tri;
            $emplacement->statut = $vehicule->statut;
            $emplacement->couleur_id = $couleur->id;
            $emplacement->save();
        }

        Schema::table('vehicules', function (Blueprint $table) {
            $table->dropPrimary('id');
            $table->foreignId('id')->references('id')->on('emplacements');

            // Supprimer designation ??? Non
            // Ou bien mettre un flag dans emplacement ???
            // Que faire en cas de suppression du véhicule ?
            // Le conserver dans le système mais plus dans les emplacements ?
            // Ajouter un field statut dans emplacement pour pouvoir désactiver
            // ceux inactif mais quand même à garder

            $table->dropColumn(['designation', 'statut', 'tri']);

            // $table->decimal('forfait', 5, 2);
            // $table->decimal('unite', 5, 2);

            // Potentiels améliorations future:
            // - Numéro chassi
            // - Date achat
            // - Fournisseur
            // - Marque
            // - ...
        });

        Schema::create('hangars', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->foreignId('id')->references('id')->on('emplacements');

            $table->string('rue');
            $table->string('no_rue');

            $table->foreignId('localite_id')->constrained();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('numero')->default('');
            $table->string('uuid')->unique();
            $table->string('achat')->unique();
            $table->string('taille')->default('');
            $table->boolean('est_etiquete')->default(false)->comment('Est étiqueté');
            $table->boolean('est_unique')->default(false);
            $table->string('remarque')->default('');
            $table->string('compartiment')->default('');

            $table->date('attribution')->nullable()->default(null);
            $table->date('retour')->nullable()->default(null);

            // TODO: Created and Deleted fields

            $table->foreignId('materiel_type_id')->constrained();
            $table->foreignId('sapeur_id')->nullable()->constrained();
            $table->foreignId('emplacement_id')->nullable()->constrained();
        });

        $materiels = MaterielPersonnel::with('materiel')->get()->toArray();
        $articles = [];
        foreach ($materiels as $materiel) {
            if ($materiel->materiel?->uuid) {
                // anciennement matériel nominal
                $articles[] = [
                    'taille' => $materiel->taille,
                    'remarque' => $materiel->remarque,
                    'materiel_type_id' => $materiel->materiel_type_id,
                    'sapeur_id' => $materiel->sapeur_id,
                    'emplacement_id' => null,
                    'est_etiquete' => false,
                    'est_unique' => true,
                    'attribution' => $materiel->attribution,
                    'retour' => $materiel->retour,

                    'numero' => $materiel->materiel->numero,
                    'uuid' => $materiel->materiel->uuid,
                    'achat' => $materiel->materiel->achat,
                ];
            } else {
                // anciennement matériel
                for ($i = 0; $i < $materiel->materiel->quantite; $i++) {
                    $articles[] = [
                        'taille' => $materiel->taille,
                        'remarque' => $materiel->remarque,
                        'materiel_type_id' => $materiel->materiel_type_id,
                        'sapeur_id' => $materiel->sapeur_id,
                        'emplacement_id' => null,
                        'est_etiquete' => false,
                        'est_unique' => true,
                        'attribution' => $materiel->attribution,
                        'retour' => $materiel->retour,

                        'numero' => '',
                        'uuid' => uniqid($materiel['materiel_type_id'] . "-"),
                        'achat' => '',
                    ];
                }
            }
        }
        Article::insert($articles);

        Schema::dropIfExists('materiel_nominals');
        Schema::dropIfExists('materiel_generiques');
        Schema::dropIfExists('materiel_personnels');

        Schema::update('materiel_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            // Champs déjà existantsapp/Infrastructure/Models/Vehicule.php
            // $table->string('designation');
            // $table->boolean('taille')->default(true);
            // $table->foreignId('materiel_categorie_id')->constrained();

            $table->string('prix');
            $table->string('fournisseur')->default('');
            $table->string('reparateur')->default('');
            $table->boolean('a_controller')->comment('Besoin de contrôller des aspects de ce matériel');
            $table->string('prefix')->default('')->comment();
            $table->string('remarque');
            $table->int('tri');

            $table->boolean('est_attribuable');

            $table->foreignId('fonction_id')->nullable()->comment('fonction responsable de l \'entretient')->constrained();
        });

        Schema::create('tuyau_diametres', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('diametre')->comment('diamètre du tuyau en mm')->unique();
        });

        Schema::create('materiel_type_batteries', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->foreign('id')->references('id')->on('materiel_types')->onDelete('cascade');
            $table->timestamps();

            $table->integer('nombre');
            $table->foreignId('batterie_type_id')->constrained();
        });

        Schema::create('materiel_type_tuyaux', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->foreign('id')->references('id')->on('materiel_types')->onDelete('cascade');
            $table->timestamps();

            $table->integer('longeur')->comment('longeur du tuyau en metre');
            $table->boolean('separement')->comment('Est-ce que le tuyau est roule separement ?');

            $table->foreignId('tuyau_diametre_id')->constrained();
        });

        Schema::create('inventaire_articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->boolean('present');

            $table->unsignedBigInteger('article_id');
            $table->foreign('article_id')->references('id')->on('articles');

            $table->unsignedBigInteger('inventaire_id');
            $table->foreign('inventaire_id')->references('id')->on('inventaires');

            // TODO: Contrainte unicité sur article_id et inventaire_id
        });

        Schema::create('maintenances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom');
            $table->int('periodicite');
            $table->boolean('externalise');

            $table->unsignedBigInteger('materiel_type_id');
            $table->foreign('materiel_type_id')->references('id')->on('materiel_types');
        });

        Schema::create('maintenance_execs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom');
            $table->string('remarque');
            $table->string('responsable');
            $table->date('date');
            $table->boolean('externalise');

            // TODO: user_id

            $table->unsignedBigInteger('maintenance_id');
            $table->foreign('maintenance_id')->references('id')->on('maintenances');
        });

        Schema::create('maintenance_exec_lignes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom');
            $table->boolean('effectuee');
            $table->boolean('reussie');
            $table->string('remarque');

            // TODO: user_id

            $table->unsignedBigInteger('maintenance_exec_id');
            $table->foreign('maintenance_exec_id')->references('id')->on('maintenance_execs');

            $table->unsignedBigInteger('article_id');
            $table->foreign('article_id')->references('id')->on('articles');
        });
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
