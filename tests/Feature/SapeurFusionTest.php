<?php

namespace Tests\Feature;

use App\Domaine\Business\SapeurBusiness;
use App\Console\Commands\SapeurFusionner;
use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\GradeSapeur;
use App\Models\GroupeSapeur;
use App\Models\Mutation;
use App\Models\ReferenceRta;
use App\Models\Sapeur;
use App\Models\SapeurTelephone;
use App\Support\Sis;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * La commande bascule la connexion par défaut sur `db_<sis>` : les données du
 * test sont créées sur `db_test`, aussi annulée en fin de test (cf. CloneSapeurTest).
 */
class SapeurFusionTest extends TestCase
{
    use DatabaseTransactions;

    private const SIS = 'test';

    protected $connectionsToTransact = ['testing', 'db_' . self::SIS];

    protected function setUp(): void
    {
        parent::setUp();
        Sis::use(self::SIS);
    }

    protected function tearDown(): void
    {
        Config::set('database.default', 'testing');
        parent::tearDown();
    }

    private function fusionner(int $sapeurId, int $civilId, array $options = [])
    {
        return $this->artisan('sapeur:fusionner', ['sapeur_id' => $sapeurId, 'civil_id' => $civilId, '--sis' => self::SIS, ...$options]);
    }

    public function testCouvreToutesLesColonnesQuiReferencentUneFiche(): void
    {
        $base = DB::getDatabaseName();
        $clesEtrangeres = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $base)->where('REFERENCED_TABLE_NAME', 'sapeurs')
            ->get(['TABLE_NAME as table', 'COLUMN_NAME as colonne']);
        $nommees = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', $base)->where('TABLE_NAME', '!=', 'sapeurs')
            ->where(fn($query) => $query->where('COLUMN_NAME', 'like', '%sapeur\_id')->orWhere('COLUMN_NAME', 'auteur_id'))
            ->get(['TABLE_NAME as table', 'COLUMN_NAME as colonne']);
        $attendues = $clesEtrangeres->concat($nommees)
            ->map(fn($c) => "{$c->table}.{$c->colonne}")
            // Référence une ligne de cours, pas une fiche
            ->reject(fn($colonne) => $colonne === 'ecritures.cours_sapeur_id')
            ->unique()->sort()->values()->all();

        $couvertes = collect(SapeurFusionner::COLONNES)
            ->flatMap(fn($colonnes, $table) => array_map(fn($colonne) => "$table.$colonne", $colonnes))
            ->sort()->values()->all();

        $this->assertSame($attendues, $couvertes);
    }

    public function testRattacheLesDonneesDuCivilEtSupprimeSaFiche(): void
    {
        $sapeur = Sapeur::factory()->create();
        $civil = Sapeur::factory()->civil()->create();
        $telephone = SapeurTelephone::factory()->create(['sapeur_id' => $civil->id]);
        $groupe = GroupeSapeur::factory()->create(['sapeur_id' => $civil->id, 'groupe_id' => 1]);

        $this->fusionner($sapeur->id, $civil->id)->assertSuccessful();

        $this->assertDatabaseMissing('sapeurs', ['id' => $civil->id], 'db_' . self::SIS);
        $this->assertSame($sapeur->id, $telephone->fresh()->sapeur_id);
        $this->assertSame($sapeur->id, $groupe->fresh()->sapeur_id);
        $this->assertSame(
            [SapeurBusiness::NATURE_SAPEUR, SapeurBusiness::NATURE_CIVIL],
            Mutation::where('sapeur_id', $sapeur->id)->pluck('nature')->sort()->values()->all(),
        );
        $sapeur->refresh();
        $this->assertSame(SapeurBusiness::STATUT_ACTIF, $sapeur->statut_sapeur);
        $this->assertSame(SapeurBusiness::STATUT_ACTIF, $sapeur->statut_civil);
    }

    public function testGardeLaLigneDuSapeurEnCasDeDoublon(): void
    {
        $sapeur = Sapeur::factory()->create();
        $civil = Sapeur::factory()->civil()->create();
        $exercice = Exercice::factory()->create();
        $convocationSapeur = ExerciceSapeur::create(['exercice_id' => $exercice->id, 'sapeur_id' => $sapeur->id, 'convoque' => 1, 'present' => 0, 'remplace' => 0]);
        $convocationCivil = ExerciceSapeur::create(['exercice_id' => $exercice->id, 'sapeur_id' => $civil->id, 'convoque' => 1, 'present' => 0, 'remplace' => 0]);
        $gradeSapeur = GradeSapeur::factory()->create(['sapeur_id' => $sapeur->id, 'grade_id' => 1]);
        $gradeCivil = GradeSapeur::factory()->create(['sapeur_id' => $civil->id, 'grade_id' => 1]);
        ReferenceRta::create(['sapeur_id' => $sapeur->id, 'data' => '{}']);
        ReferenceRta::create(['sapeur_id' => $civil->id, 'data' => '{}']);

        $this->fusionner($sapeur->id, $civil->id)
            ->expectsOutputToContain('Doublons du civil supprimés')
            ->assertSuccessful();

        $this->assertNotNull($convocationSapeur->fresh());
        $this->assertNull($convocationCivil->fresh());
        $this->assertNotNull($gradeSapeur->fresh());
        $this->assertNull($gradeCivil->fresh());
        $this->assertSame(1, ReferenceRta::where('sapeur_id', $sapeur->id)->count());
        $this->assertFalse(ReferenceRta::where('sapeur_id', $civil->id)->exists());
    }

    public function testRepriseLaReferenceRtaDuCivilSiLeSapeurNenAPas(): void
    {
        $sapeur = Sapeur::factory()->create();
        $civil = Sapeur::factory()->civil()->create();
        ReferenceRta::create(['sapeur_id' => $civil->id, 'data' => '{}']);

        $this->fusionner($sapeur->id, $civil->id)->assertSuccessful();

        $this->assertTrue(ReferenceRta::where('sapeur_id', $sapeur->id)->exists());
    }

    public function testCompleteLesChampsVidesEtGardeLaDateDeNaissanceDuSapeur(): void
    {
        $sapeur = Sapeur::factory()->create(['iban' => '', 'iban_statut' => 1, 'profession' => 'Menuisier', 'date_naissance' => '1980-05-10']);
        $civil = Sapeur::factory()->civil()->create(['iban' => 'CH9300762011623852957', 'iban_statut' => 2, 'profession' => 'Ébéniste', 'date_naissance' => now()->subDay()->toDateString()]);

        $this->fusionner($sapeur->id, $civil->id)
            ->expectsOutputToContain('Champs différents')
            ->expectsOutputToContain('Ébéniste')
            ->assertSuccessful();

        $sapeur->refresh();
        $this->assertSame('CH9300762011623852957', $sapeur->iban);
        $this->assertSame(2, (int) $sapeur->iban_statut);
        $this->assertSame('Menuisier', $sapeur->profession);
        $this->assertSame('1980-05-10', substr($sapeur->date_naissance, 0, 10));
    }

    public function testDryRunNeModifieRien(): void
    {
        $sapeur = Sapeur::factory()->create();
        $civil = Sapeur::factory()->civil()->create();
        $telephone = SapeurTelephone::factory()->create(['sapeur_id' => $civil->id]);

        $this->fusionner($sapeur->id, $civil->id, ['--dry-run' => true])
            ->expectsOutputToContain('[dry-run]')
            ->assertSuccessful();

        $this->assertNotNull($civil->fresh());
        $this->assertSame($civil->id, $telephone->fresh()->sapeur_id);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function fichesInvalides(): array
    {
        return [
            'premier jamais sapeur' => ['civil', 'civil'],
            'second ancien sapeur' => ['sapeur', 'ancienSapeurCivil'],
            'second recrue' => ['sapeur', 'recrue'],
        ];
    }

    #[DataProvider('fichesInvalides')]
    public function testRefuseDesFichesInvalides(string $etatPremier, string $etatSecond): void
    {
        $premier = $etatPremier === 'sapeur' ? Sapeur::factory()->create() : Sapeur::factory()->$etatPremier()->create();
        $second = Sapeur::factory()->$etatSecond()->create();

        $this->fusionner($premier->id, $second->id)->assertFailed();

        $this->assertNotNull($second->fresh());
    }

    public function testRefuseLaMemeFiche(): void
    {
        $sapeur = Sapeur::factory()->create();

        $this->fusionner($sapeur->id, $sapeur->id)
            ->expectsOutputToContain('Les deux fiches doivent être différentes.')
            ->assertFailed();
    }

    public function testRefuseDesPeriodesCivilesQuiSeChevauchent(): void
    {
        // Les deux fiches ont chacune une période civile en cours
        $sapeur = Sapeur::factory()->ancienSapeurCivil()->create();
        $civil = Sapeur::factory()->civil()->create();

        $this->fusionner($sapeur->id, $civil->id)->assertFailed();

        $this->assertNotNull($civil->fresh());
    }

    public function testCommandeFusionneEtRappelleLeCompteAuth(): void
    {
        $sapeur = Sapeur::factory()->create();
        $civil = Sapeur::factory()->civil()->create();

        $this->fusionner($sapeur->id, $civil->id)
            ->expectsOutputToContain("Fiche {$civil->id} fusionnée dans la fiche {$sapeur->id}")
            ->expectsOutputToContain('GestSIS_Auth')
            ->assertSuccessful();

        $this->assertNull($civil->fresh());
    }

    public function testCommandeRefuseUnSisInconnu(): void
    {
        $this->artisan('sapeur:fusionner', ['sapeur_id' => 1, 'civil_id' => 2, '--sis' => 'inconnu'])
            ->expectsOutputToContain('SIS inconnu')
            ->assertFailed();
    }
}
