<?php

namespace Tests\Feature;

use App\Models\Exercice;
use App\Models\Sapeur;
use App\Support\Sis;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * La commande bascule la connexion par défaut sur `db_<sis>` : les données du
 * test sont créées sur `db_test`, aussi annulée en fin de test (cf. DbsControlesNonNumerotesTest).
 */
class DbsMigrerHeuresExerciceTest extends TestCase
{
    use DatabaseTransactions;

    private const SIS = 'test';

    protected $connectionsToTransact = ['testing', 'db_' . self::SIS];

    private int $sapeurId;

    protected function setUp(): void
    {
        parent::setUp();
        Sis::use(self::SIS);
        $this->sapeurId = Sapeur::factory()->create()->id;
    }

    protected function tearDown(): void
    {
        Config::set('database.default', 'testing');
        parent::tearDown();
    }

    private function type(string $designation, float $montant = 25): int
    {
        return DB::table('heure_exercice_types')->insertGetId([
            'designation' => $designation,
            'montant' => $montant,
            'type' => 1,
            'compte_id' => 1,
            'ecriture_categorie_id' => 1,
            'type_unite_id' => 2,
        ]);
    }

    private function heure(int $exerciceId, ?int $typeId, string $designation, float $quantite): int
    {
        return DB::table('heure_exercices')->insertGetId([
            'designation' => $designation,
            'quantite' => $quantite,
            'montant' => 25,
            'type' => 1,
            'compte_id' => 1,
            'ecriture_categorie_id' => 1,
            'type_unite_id' => 2,
            'exercice_id' => $exerciceId,
            'sapeur_id' => $this->sapeurId,
            'heure_exercice_type_id' => $typeId,
            'created_at' => '2026-03-02 19:30:00',
        ]);
    }

    private function ecriture(Exercice $exercice, string $designation, float $quantite): int
    {
        return DB::table('ecritures')->insertGetId([
            'designation' => $designation,
            'total' => $quantite * 25,
            'tarif' => 25,
            'quantite' => $quantite,
            'type_unite_id' => 2,
            'type' => 1,
            'sapeur_id' => $this->sapeurId,
            'compte_id' => 1,
            'ecriture_categorie_id' => 1,
            'exercice_comptable_id' => $exercice->exercice_comptable_id,
            'exercice_id' => $exercice->id,
            'module' => 1,
        ]);
    }

    private function migrer()
    {
        return $this->artisan('dbs:migrer-heures-exercice', ['--sis' => [self::SIS]]);
    }

    public function testConvertitTypesEtHeures(): void
    {
        $typeId = $this->type('Préparation conversion');
        $valide = Exercice::factory()->create(['statut' => 3]);
        $impute = Exercice::factory()->create(['statut' => 4]);
        $this->heure($valide->id, $typeId, 'Préparation conversion', 1.5);
        $this->heure($impute->id, $typeId, 'Préparation conversion', 2);

        $this->migrer()->assertExitCode(0);

        $types = DB::table('travail_types')->where('designation', 'Préparation conversion')->get();
        $this->assertCount(1, $types);
        $this->assertSame(1, (int) $types[0]->lien_exercice);
        $this->assertSame(1, (int) $types[0]->lien_obligatoire);
        $ligne = DB::table('travail_type_fonctions')->where('travail_type_id', $types[0]->id)->get();
        $this->assertCount(1, $ligne);
        $this->assertEquals(25, $ligne[0]->tarif);
        $this->assertSame(1, (int) DB::table('travaux')->where('exercice_id', $valide->id)->value('statut'));
        $this->assertSame(2, (int) DB::table('travaux')->where('exercice_id', $impute->id)->value('statut'));
        $this->assertSame(0, DB::table('heure_exercices')->count());
        $this->assertSame(0, DB::table('heure_exercice_types')->count());
    }

    public function testDeuxiemeExecutionNeConvertitRien(): void
    {
        $exercice = Exercice::factory()->create(['statut' => 2]);
        $this->heure($exercice->id, $this->type('Préparation double'), 'Préparation double', 1);

        $this->migrer()->assertExitCode(0);
        $this->migrer()->assertExitCode(0);

        $this->assertSame(1, DB::table('travaux')->where('exercice_id', $exercice->id)->count());
        $this->assertSame(1, DB::table('travail_types')->where('designation', 'Préparation double')->count());
    }

    public function testSisInconnuEchoue(): void
    {
        $this->artisan('dbs:migrer-heures-exercice', ['--sis' => ['inconnu']])->assertExitCode(1);
    }

    public function testTypeHomonymeSuffixe(): void
    {
        $existant = DB::table('travail_types')->insertGetId([
            'designation' => 'Roulage homonyme', 'actif' => true, 'ecriture_categorie_id' => 1, 'type_unite_id' => 2,
        ]);
        $exercice = Exercice::factory()->create(['statut' => 2]);
        $this->heure($exercice->id, $this->type('Roulage homonyme'), 'Roulage homonyme', 1);

        $this->migrer()->assertExitCode(0);

        $this->assertSame(1, DB::table('travail_types')->where('designation', 'Roulage homonyme (exercice)')->count());
        $this->assertSame(0, (int) DB::table('travail_types')->where('id', $existant)->value('lien_exercice'));
        $typeFiche = DB::table('travaux')->where('exercice_id', $exercice->id)->value('travail_type_id');
        $this->assertNotSame($existant, (int) $typeFiche);
    }

    public function testHeureSansTypeUtiliseSaCopieDuTarif(): void
    {
        $exercice = Exercice::factory()->create(['statut' => 2]);
        $this->heure($exercice->id, null, 'Ancien type orphelin', 1);

        $this->migrer()->assertExitCode(0);

        $type = DB::table('travail_types')->where('designation', 'Ancien type orphelin')->first();
        $this->assertNotNull($type);
        $this->assertSame((int) $type->id, (int) DB::table('travaux')->where('exercice_id', $exercice->id)->value('travail_type_id'));
    }

    public function testRattacheLesEcrituresDeLExerciceImpute(): void
    {
        $exercice = Exercice::factory()->create(['statut' => 4]);
        $this->heure($exercice->id, $this->type('Préparation rattachée'), 'Thème ARI', 1.5);
        $ecritureHeure = $this->ecriture($exercice, 'Neuchâtel (Hangar) Exercice section 1 - Thème ARI', 1.5);
        $ecriturePresence = $this->ecriture($exercice, 'Neuchâtel (Hangar) Exercice section 1', 1.5);

        $this->migrer()->assertExitCode(0);

        $travailId = (int) DB::table('travaux')->where('exercice_id', $exercice->id)->value('id');
        $ecriture = DB::table('ecritures')->find($ecritureHeure);
        $this->assertSame($travailId, (int) $ecriture->travail_id);
        $this->assertSame(6, (int) $ecriture->module);
        $this->assertSame('Neuchâtel (Hangar) Exercice section 1', $ecriture->designation);
        $this->assertSame('Préparation rattachée - Thème ARI', $ecriture->complement);
        $this->assertEquals(37.5, $ecriture->total);
        $presence = DB::table('ecritures')->find($ecriturePresence);
        $this->assertNull($presence->travail_id);
        $this->assertSame(1, (int) $presence->module);
    }

    public function testEcrituresIdentiquesRepartiesUneParFiche(): void
    {
        $exercice = Exercice::factory()->create(['statut' => 4]);
        $typeId = $this->type('Rédition identique');
        $this->heure($exercice->id, $typeId, 'Rédition', 2);
        $this->heure($exercice->id, $typeId, 'Rédition', 2);
        $this->ecriture($exercice, 'Exercice - Rédition', 2);
        $this->ecriture($exercice, 'Exercice - Rédition', 2);

        $this->migrer()->assertExitCode(0);

        $travaux = DB::table('travaux')->where('exercice_id', $exercice->id)->pluck('id')->map(fn($id) => (int) $id)->sort()->values()->all();
        $rattachees = DB::table('ecritures')->where('exercice_id', $exercice->id)->pluck('travail_id')->map(fn($id) => (int) $id)->sort()->values()->all();
        $this->assertCount(2, $travaux);
        $this->assertSame($travaux, $rattachees);
    }

    public function testDryRunNeModifieRienEtSignaleLesEcrituresNonReliees(): void
    {
        $exercice = Exercice::factory()->create(['statut' => 4]);
        $this->heure($exercice->id, $this->type('Préparation essai'), 'Thème ARI', 1.5);
        // Quantité différente : ni l'heure ni l'écriture ne peuvent être reliées
        $ecritureId = $this->ecriture($exercice, 'Exercice section 1 - Thème ARI', 3);
        $heuresAvant = DB::table('heure_exercices')->count();
        $travauxAvant = DB::table('travaux')->count();

        $this->artisan('dbs:migrer-heures-exercice', ['--sis' => [self::SIS], '--dry-run' => true])
            ->expectsOutputToContain("exercice {$exercice->id}, sapeur {$this->sapeurId}, Thème ARI")
            ->expectsOutputToContain("écriture {$ecritureId}")
            ->assertExitCode(0);

        $this->assertSame($heuresAvant, DB::table('heure_exercices')->count());
        $this->assertSame($travauxAvant, DB::table('travaux')->count());
        $this->assertNull(DB::table('ecritures')->where('id', $ecritureId)->value('travail_id'));
    }

    public function testEcritureIntrouvableSignalee(): void
    {
        $exercice = Exercice::factory()->create(['statut' => 4]);
        $this->heure($exercice->id, $this->type('Préparation orpheline'), 'Sans écriture', 1);

        $this->migrer()
            ->expectsOutputToContain("exercice {$exercice->id}, sapeur {$this->sapeurId}, Sans écriture")
            ->assertExitCode(0);

        $this->assertSame(1, DB::table('travaux')->where('exercice_id', $exercice->id)->count());
    }
}
