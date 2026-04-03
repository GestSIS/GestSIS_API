<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Sapeur;
use App\Models\CoursSapeur;
use App\Models\FonctionSapeur;
use App\Models\GradeSapeur;
use App\Models\Permis;
use App\Models\SapeurTelephone;
use App\Models\Mutation;

class CloneSapeur extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sapeur:clone {sapeur_id} {source_sis} {target_sis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clone un sapeur et ses données (cours, fonctions, grades, permis) d\'une base SIS vers une autre';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sapeurId = $this->argument('sapeur_id');
        $sourceSis = $this->argument('source_sis');
        $targetSis = $this->argument('target_sis');

        // Vérifier que les deux SIS existent dans la configuration
        $dbs = config('database.dbs');
        if (!in_array($sourceSis, $dbs)) {
            $this->error("La base source '{$sourceSis}' n'existe pas dans la configuration.");
            return 1;
        }
        if (!in_array($targetSis, $dbs)) {
            $this->error("La base cible '{$targetSis}' n'existe pas dans la configuration.");
            return 1;
        }

        $this->info("Clonage du sapeur {$sapeurId} de {$sourceSis} vers {$targetSis}...");

        // Connexion à la base source
        Config::set('database.default', 'db_' . $sourceSis);

        // Récupérer le sapeur source avec toutes ses relations
        $sapeur = Sapeur::find($sapeurId);
        if (!$sapeur) {
            $this->error("Sapeur {$sapeurId} non trouvé dans la base {$sourceSis}.");
            return 1;
        }

        $this->info("Sapeur trouvé: {$sapeur->nom} {$sapeur->prenom}");

        // Récupérer toutes les données associées
        $cours = CoursSapeur::where('sapeur_id', $sapeurId)->get();
        $fonctions = FonctionSapeur::where('sapeur_id', $sapeurId)->get();
        $grades = GradeSapeur::where('sapeur_id', $sapeurId)->get();
        $permis = Permis::where('sapeur_id', $sapeurId)->get();
        $telephones = SapeurTelephone::where('sapeur_id', $sapeurId)->get();

        $this->info("Données récupérées:");
        $this->info("  - Cours: {$cours->count()}");
        $this->info("  - Fonctions: {$fonctions->count()}");
        $this->info("  - Grades: {$grades->count()}");
        $this->info("  - Permis: {$permis->count()}");
        $this->info("  - Téléphones: {$telephones->count()}");

        // Connexion à la base cible pour vérifier les IDs disponibles
        Config::set('database.default', 'db_' . $targetSis);

        // Récupérer les IDs valides dans la base cible
        $validCoursIds = DB::table('cours')->pluck('id')->toArray();
        $validFonctionIds = DB::table('fonctions')->pluck('id')->toArray();
        $validGradeIds = DB::table('grades')->pluck('id')->toArray();
        $validPermisTypeIds = DB::table('permis_types')->pluck('id')->toArray();

        DB::beginTransaction();
        try {
            // Créer le sapeur dans la base cible
            $newSapeur = new Sapeur();
            $newSapeur->fill($sapeur->getAttributes());
            unset($newSapeur->id); // Laisser auto-increment générer un nouvel ID
            $newSapeur->save();

            $newSapeurId = $newSapeur->id;
            $this->info("Sapeur cloné avec le nouvel ID: {$newSapeurId}");

            // Cloner les cours (uniquement si le cours existe dans la base cible)
            $coursCloned = 0;
            $coursSkipped = 0;
            foreach ($cours as $c) {
                if (in_array($c->cours_id, $validCoursIds)) {
                    $newCours = new CoursSapeur();
                    $newCours->fill($c->getAttributes());
                    $newCours->sapeur_id = $newSapeurId;
                    $newCours->cours_id = $c->cours_id;
                    unset($newCours->id);
                    $newCours->save();
                    $coursCloned++;
                } else {
                    $coursSkipped++;
                }
            }
            $this->info("  ✓ {$coursCloned} cours clonés" . ($coursSkipped > 0 ? " ({$coursSkipped} ignorés car inexistants dans la cible)" : ""));

            // Cloner les fonctions (uniquement si la fonction existe dans la base cible)
            $fonctionsCloned = 0;
            $fonctionsSkipped = 0;
            foreach ($fonctions as $f) {
                if (in_array($f->fonction_id, $validFonctionIds)) {
                    $newFonction = new FonctionSapeur();
                    $newFonction->fill($f->getAttributes());
                    $newFonction->sapeur_id = $newSapeurId;
                    $newFonction->fonction_id = $f->fonction_id;
                    unset($newFonction->id);
                    $newFonction->save();
                    $fonctionsCloned++;
                } else {
                    $fonctionsSkipped++;
                }
            }
            $this->info("  ✓ {$fonctionsCloned} fonctions clonées" . ($fonctionsSkipped > 0 ? " ({$fonctionsSkipped} ignorées car inexistantes dans la cible)" : ""));

            // Cloner les grades (uniquement si le grade existe dans la base cible)
            $gradesCloned = 0;
            $gradesSkipped = 0;
            foreach ($grades as $g) {
                if (in_array($g->grade_id, $validGradeIds)) {
                    $newGrade = new GradeSapeur();
                    $newGrade->fill($g->getAttributes());
                    $newGrade->sapeur_id = $newSapeurId;
                    $newGrade->grade_id = $g->grade_id;
                    unset($newGrade->id);
                    $newGrade->save();
                    $gradesCloned++;
                } else {
                    $gradesSkipped++;
                }
            }
            $this->info("  ✓ {$gradesCloned} grades clonés" . ($gradesSkipped > 0 ? " ({$gradesSkipped} ignorés car inexistants dans la cible)" : ""));

            // Cloner les permis (uniquement si le type de permis existe dans la base cible)
            $permisCloned = 0;
            $permisSkipped = 0;
            foreach ($permis as $p) {
                if (in_array($p->permis_type_id, $validPermisTypeIds)) {
                    $newPermis = new Permis();
                    $newPermis->fill($p->getAttributes());
                    $newPermis->sapeur_id = $newSapeurId;
                    $newPermis->permis_type_id = $p->permis_type_id;
                    unset($newPermis->id);
                    $newPermis->save();
                    $permisCloned++;
                } else {
                    $permisSkipped++;
                }
            }
            $this->info("  ✓ {$permisCloned} permis clonés" . ($permisSkipped > 0 ? " ({$permisSkipped} ignorés car type inexistant dans la cible)" : ""));

            // Cloner les téléphones
            foreach ($telephones as $t) {
                $newTelephone = new SapeurTelephone();
                $newTelephone->fill($t->getAttributes());
                $newTelephone->sapeur_id = $newSapeurId;
                unset($newTelephone->id);
                $newTelephone->save();
            }
            $this->info("  ✓ {$telephones->count()} téléphones clonés");

            // Créer une nouvelle mutation (incorporation aujourd'hui)
            $newMutation = new Mutation();
            $newMutation->sapeur_id = $newSapeurId;
            $newMutation->incorporation = now()->format('Y-m-d');
            $newMutation->sortie = null;
            $newMutation->motif = 'Incorporation';
            $newMutation->save();
            $this->info("  ✓ Nouvelle mutation créée (incorporation: " . now()->format('d.m.Y') . ")");

            DB::commit();
            $this->info("✅ Clonage terminé avec succès!");
            $this->info("Ancien ID: {$sapeurId} → Nouvel ID: {$newSapeurId}");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Erreur lors du clonage: " . $e->getMessage());
            return 1;
        }
    }
}
