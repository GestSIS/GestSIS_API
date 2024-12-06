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

return new class extends Migration {
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
        $couleur = couleur::create(['nom' => 'default', 'texte' => '#ffffffff', 'fond' => '#fc031780']);

        // Déjà existante
        Schema::table('materiel_categories', function (Blueprint $table) {
            $table->integer('tri')->default(1);

            $table->unsignedBigInteger('couleur_id')->default(1);
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

        Schema::create('emplacements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->integer('tri');
            $table->string('remarque')->default('');
            $table->boolean('est_etiquete')->default(false)->comment('Est-ce que les articles dans cet inventaire portent une étiquette');
            $table->date('impression_inventaire')->nullable()->default(null);

            $table->foreignId('couleur_id')->constrained();

            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('emplacements');

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
            $emplacement->couleur_id = 1; //$couleur->id;
            $emplacement->save();
        }

        Schema::table('vehicules', function (Blueprint $table) {
            // $table->dropPrimary('id');
            // $table->dropForeign('intervention_vehicule_vehicule_id_foreign');
            // $table->unsignedBigInteger('id')->unique()->change();
            $table->foreign('id')->references('id')->on('emplacements');

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
            $table->unsignedBigInteger('id')->unique();
            $table->foreign('id')->references('id')->on('emplacements');

            $table->string('rue');
            $table->string('no_rue');

            $table->foreignId('localite_id')->constrained();
        });

        Schema::dropIfExists('articles');
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
            if ($materiel["materiel"]["uuid"] ?? false) {
                // anciennement matériel nominal
                $articles[] = [
                    'taille' => $materiel["taille"],
                    'remarque' => $materiel["remarque"],
                    'materiel_type_id' => $materiel["materiel_type_id"],
                    'sapeur_id' => $materiel["sapeur_id"],
                    'emplacement_id' => null,
                    'est_etiquete' => false,
                    'est_unique' => true,
                    'attribution' => $materiel["attribution"],
                    'retour' => $materiel["retour"],

                    'numero' => $materiel["materiel"]["numero"],
                    'uuid' => $materiel["materiel"]["uuid"],
                    'achat' => $materiel["materiel"]["achat"],
                ];
            } else {
                // anciennement matériel
                for ($i = 0; $i < $materiel["materiel"]["quantite"]; $i++) {
                    $articles[] = [
                        'taille' => $materiel["taille"],
                        'remarque' => $materiel["remarque"],
                        'materiel_type_id' => $materiel["materiel_type_id"],
                        'sapeur_id' => $materiel["sapeur_id"],
                        'emplacement_id' => null,
                        'est_etiquete' => false,
                        'est_unique' => true,
                        'attribution' => $materiel["attribution"],
                        'retour' => $materiel["retour"],

                        'numero' => '',
                        'uuid' => uniqid($materiel['materiel_type_id'] . "-"),
                        'achat' => '',
                    ];
                }
            }
        }
        Article::insert($articles);

        Schema::table('materiel_types', function (Blueprint $table) {

            // Champs déjà existants app/Infrastructure/Models/Vehicule.php
            // $table->bigIncrements('id');
            // $table->timestamps();

            // $table->string('designation');
            // $table->boolean('taille')->default(true);
            // $table->foreignId('materiel_categorie_id')->constrained();

            $table->string('prix');
            $table->string('fournisseur')->default('');
            $table->string('reparateur')->default('');
            $table->boolean('a_controller')->comment('Besoin de contrôller des aspects de ce matériel');
            $table->string('remarque')->default('');
            $table->integer('tri');

            $table->string('prefix')->default('')->comment();
            $table->boolean('est_numerote')->default(false);
            $table->boolean('est_attribuable')->default(false);
            $table->boolean('est_taillee')->default(false)->comment('Possède une taille');

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

        // TODO: Migrate event_type non validable dans maintenance_type
        // TODO: Migrate event_type validable dans maintenance_type

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

        Schema::dropIfExists('materiel_alerte_type_pour');
        Schema::dropIfExists('materiel_alerte_types');
        Schema::dropIfExists('materiel_event_type_pour');
        Schema::dropIfExists('materiel_events');
        Schema::dropIfExists('materiel_event_types');
        Schema::dropIfExists('materiel_alertes');

        Schema::dropIfExists('materiel_nominals');
        Schema::dropIfExists('materiel_generiques');
        Schema::dropIfExists('materiel_personnels');
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
