<?php

namespace App\Console\Commands;

use App\Models\Cours;
use App\Models\Grade;
use App\Support\Sis;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DbsSeed extends Command
{
    private const CATEGORIE = 'Protection Respiratoire';

    private const TYPE_MASQUE = 'Masque Dräger';
    private const TYPE_SOUPAPE = 'Soupape à la demande Dräger';
    private const TYPE_APPAREIL = 'Appareil à air comprimé Dräger';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbs:seed-custom {--sis=* : Limiter à ces SIS}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed les bases de données';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sisDemandes = $this->option('sis');
        foreach ($sisDemandes as $sis) {
            if (!Sis::isValid($sis)) {
                $this->error("SIS inconnu : {$sis}");

                return self::FAILURE;
            }
        }

        $resultat = self::SUCCESS;

        Sis::each(function ($db) use (&$resultat) {
            try {
                $this->creerControlesApr();
                $this->info("SIS {$db} : contrôles APR en place");
            } catch (RuntimeException $e) {
                $this->error("SIS {$db} : {$e->getMessage()}");
                $resultat = self::FAILURE;
            }

            // Artisan::call('db:seed --database=' . Sis::connection($db));
            // $grade = Grade::where('abreviation', '=', 'Adj')->first() ?? Grade::where('abreviation', '=', 'adj')->first();
            // if ($grade === null) {
            //     try {
            //         Grade::insert(['id' => 12, 'designation' => 'Adjudant', 'abreviation' => 'Adj', 'groupe' => 2, 'tri' => 67]);
            //     } catch (Exception $e) {
            //         printf("Unable to create grade for db : " . $db);
            //     }
            // }

            // Cours::where('designation', '=', 'Chef d\'intervention 1')->update(['grade_id' => $grade?->id ?? 12]);
            // Cours::where('designation', '=', 'Chef d\'intervention 2')->update(['grade_id' => 3]);
            // Cours::where('designation', '=', 'Machiniste')->update(['grade_id' => 7]);
            // Cours::where('designation', '=', 'Chef de groupe')->update(['grade_id' => 6]);
            // if jsp dans $db
            // if (str_contains($db, 'jsp')) {
            //     printf("Seeding SIS $db\n");
            //     Cours::insert([
            //         ['designation' => 'JSP Module 1', 'abreviation' => 'JSP 1', 'tri' => 1],
            //         ['designation' => 'JSP Module 2', 'abreviation' => 'JSP 2', 'tri' => 2],
            //         ['designation' => 'JSP Module 3', 'abreviation' => 'JSP 3', 'tri' => 3],
            //         ['designation' => 'JSP Module 4', 'abreviation' => 'JSP 4', 'tri' => 4],
            //         ['designation' => 'JSP Module 5', 'abreviation' => 'JSP 5', 'tri' => 5],
            //     ]);
            // }
            printf("\n");
        }, $sisDemandes === [] ? null : $sisDemandes);
        printf("Migrating done\n");
        return $resultat;
    }

    /**
     * Reprend les feuilles de contrôle Dräger Schweiz (édition 2020). Elles portent sur
     * le contrôle annuel, qui comprend toutes les colonnes ; la date, la remarque et le
     * visa de ces feuilles sont les champs de l'exécution.
     *
     * Les types sont retrouvés par leur désignation et un contrôle déjà présent sous ce
     * nom n'est pas modifié.
     *
     * @throws RuntimeException si la catégorie des types à créer n'existe pas
     */
    private function creerControlesApr(): void
    {
        DB::transaction(function (): void {
            foreach ($this->controles() as $controle) {
                if (DB::table('controles')->where('nom', $controle['nom'])->exists()) {
                    continue;
                }

                $controleId = DB::table('controles')->insertGetId([
                    'nom' => $controle['nom'],
                    'description' => $controle['description'],
                    'recurrence_type' => 'NON_PERIODIQUE',
                    'recurrence_value' => null,
                    'duree_preavis' => null,
                    'nb_execution_max' => null,
                    'nb_execution_preavis' => null,
                    'externe' => false,
                    'reparateur' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('controle_materiel_types')->insert([
                    'controle_id' => $controleId,
                    'materiel_type_id' => $this->typeId($controle['type']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($controle['taches'] as $index => $tache) {
                    $this->creerTache($controleId, $index + 1, $tache);
                }
            }
        });
    }

    /**
     * @param array{nom: string, description: ?string, type: string, obligatoire?: bool, unit?: string, value_min?: float|int, value_max?: float|int, composant?: string} $tache
     */
    private function creerTache(int $controleId, int $order, array $tache): void
    {
        $tacheId = DB::table('controle_taches')->insertGetId([
            'controle_id' => $controleId,
            'order' => $order,
            'nom' => $tache['nom'],
            'description' => $tache['description'],
            'type' => $tache['type'],
            'obligatoire' => $tache['obligatoire'] ?? true,
            'unit' => $tache['unit'] ?? null,
            'value_min' => $tache['value_min'] ?? null,
            'value_max' => $tache['value_max'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (isset($tache['composant'])) {
            DB::table('controle_tache_materiel_types')->insert([
                'controle_tache_id' => $tacheId,
                'materiel_type_id' => $this->typeId($tache['composant']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function typeId(string $designation): int
    {
        $typeId = DB::table('materiel_types')->where('designation', $designation)->value('id');

        if ($typeId !== null) {
            return $typeId;
        }

        $categorieId = DB::table('materiel_categories')->where('designation', self::CATEGORIE)->value('id');

        if ($categorieId === null) {
            throw new RuntimeException('Catégorie de matériel introuvable : ' . self::CATEGORIE);
        }

        return DB::table('materiel_types')->insertGetId([
            'tri' => DB::table('materiel_types')->max('tri') + 1,
            'materiel_categorie_id' => $categorieId,
            'type' => 0,
            'est_emplacement' => false,
            'designation' => $designation,
            'est_numerote' => true,
            'est_attribuable' => $designation === self::TYPE_MASQUE,
            'est_taillee' => false,
        ]);
    }

    /**
     * @return array<int, array{nom: string, description: string, type: string, taches: array<int, array<string, mixed>>}>
     */
    private function controles(): array
    {
        return [
            [
                'nom' => 'APR - Masque',
                'description' => 'Feuille de contrôle Dräger pour masques avec surpression.',
                'type' => self::TYPE_MASQUE,
                'taches' => [
                    ['nom' => "Contrôle d'étanchéité", 'description' => "Avec l'appareil de contrôle à -10 mbar.", 'type' => 'NUMERIC', 'unit' => 'mbar/min', 'value_max' => 1],
                    ['nom' => "Pression d'ouverture de la soupape expiratoire", 'description' => 'Flow 10 l/min.', 'type' => 'NUMERIC', 'unit' => 'mbar', 'value_min' => 4.2, 'value_max' => 5.7],
                    ['nom' => 'Contrôle visuel du masque', 'description' => 'État général.', 'type' => 'BOOLEAN'],
                ],
            ],
            [
                'nom' => 'APR - Soupape à la demande',
                'description' => 'Feuille de contrôle Dräger pour soupape à la demande avec surpression.',
                'type' => self::TYPE_SOUPAPE,
                'taches' => [
                    ['nom' => 'Test général de fonction', 'description' => "Actionner 2 x le mécanisme de surpression, l'air doit s'échapper librement.", 'type' => 'BOOLEAN'],
                    ['nom' => "Contrôle d'étanchéité vanne de dosage", 'description' => "Contrôle d'augmentation de pression.", 'type' => 'NUMERIC', 'unit' => 'mbar/min', 'value_min' => 0, 'value_max' => 8],
                    ['nom' => 'Pression de commutation', 'description' => 'Contrôle fonctionnel.', 'type' => 'BOOLEAN'],
                    ['nom' => 'Surpression', 'description' => 'Pression statique.', 'type' => 'NUMERIC', 'unit' => 'mbar', 'value_min' => 1, 'value_max' => 3.9],
                    ['nom' => "Contrôle d'étanchéité", 'description' => 'Étanchéité à basse pression. Générer une surpression de +/- 7,0 mbar.', 'type' => 'NUMERIC', 'unit' => 'mbar/min', 'value_max' => 1],
                ],
            ],
            [
                'nom' => 'APR - Appareil',
                'description' => 'Feuille de contrôle Dräger pour appareils à air comprimé.',
                'type' => self::TYPE_APPAREIL,
                'taches' => [
                    ['nom' => 'Contrôle batterie(s)', 'description' => 'Indiquer en remarque si la ou les batteries ont été changées.', 'type' => 'BOOLEAN'],
                    ['nom' => 'Pression bouteille gauche', 'description' => 'Bouteille 200 / 300 bar.', 'type' => 'NUMERIC', 'unit' => 'bar'],
                    ['nom' => 'Pression bouteille droite', 'description' => 'Bouteille 200 / 300 bar.', 'type' => 'NUMERIC', 'obligatoire' => false, 'unit' => 'bar'],
                    ['nom' => "Manomètre de l'appareil", 'description' => null, 'type' => 'NUMERIC', 'unit' => 'bar'],
                    ['nom' => 'Différence bouteille et manomètre', 'description' => null, 'type' => 'NUMERIC', 'unit' => 'bar', 'value_min' => -10, 'value_max' => 10],
                    ['nom' => 'Soupape à la demande', 'description' => null, 'type' => 'COMPOSANT', 'composant' => self::TYPE_SOUPAPE],
                    ['nom' => 'Moyenne pression', 'description' => '½ pression de remplissage.', 'type' => 'NUMERIC', 'unit' => 'bar', 'value_min' => 6, 'value_max' => 9],
                    ['nom' => 'Signal avertisseur', 'description' => null, 'type' => 'NUMERIC', 'unit' => 'bar', 'value_min' => 50, 'value_max' => 60],
                    ['nom' => "Contrôle d'étanchéité", 'description' => 'Perte de pression maximale.', 'type' => 'NUMERIC', 'unit' => 'bar/min', 'value_max' => 10],
                    ['nom' => 'Cornet acoustique', 'description' => null, 'type' => 'BOOLEAN'],
                    ['nom' => 'Contrôle visuel appareil et masque', 'description' => 'État général.', 'type' => 'BOOLEAN'],
                    ['nom' => 'Masque', 'description' => null, 'type' => 'COMPOSANT', 'obligatoire' => false, 'composant' => self::TYPE_MASQUE],
                ],
            ],
        ];
    }
}
