<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\Article;
use App\Models\MaterielType;
use App\Support\Sis;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * dbs:fix parcourt tous les SIS : chaque connexion `db_<sis>` est mise en
 * transaction (annulée en fin de test), et les données du test sont créées
 * sur `db_test`, comme dans CloneSapeurTest.
 */
class DbsFixVehiculesNumerotesTest extends TestCase
{
    use DatabaseTransactions;

    private const SIS = 'test';

    /**
     * @return array<int, string>
     */
    protected function connectionsToTransact(): array
    {
        return ['testing', ...array_map(Sis::connection(...), Sis::keys())];
    }

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

    private function creerTypeVehicule(): MaterielType
    {
        return MaterielType::factory()->create([
            'type' => MaterielTypeBusiness::TYPE_VEHICULE,
            'est_emplacement' => true,
            'est_numerote' => false,
        ]);
    }

    public function testLesTypesVehiculeDeviennentNumerotes(): void
    {
        $vehicule = $this->creerTypeVehicule();
        $autre = MaterielType::factory()->create(['est_numerote' => false]);

        $this->artisan('dbs:fix')->assertExitCode(0);

        $this->assertTrue($vehicule->fresh()->est_numerote);
        $this->assertFalse($autre->fresh()->est_numerote);
    }

    public function testLeNumeroDesVehiculesEstRempliAvecLImmatriculationSinonLaDesignation(): void
    {
        $type = $this->creerTypeVehicule();
        $avecPlaque = Article::factory()->create([
            'materiel_type_id' => $type->id, 'numero' => '', 'immatriculation' => 'JU 1234', 'designation' => 'TP 1',
        ]);
        $sansPlaque = Article::factory()->create([
            'materiel_type_id' => $type->id, 'numero' => ' ', 'immatriculation' => '  ', 'designation' => 'Remorque',
        ]);
        $dejaNumerote = Article::factory()->create([
            'materiel_type_id' => $type->id, 'numero' => 'V-07', 'immatriculation' => 'JU 9999', 'designation' => 'TP 2',
        ]);
        $nonVehicule = Article::factory()->create([
            'materiel_type_id' => MaterielType::factory()->create()->id, 'numero' => '', 'designation' => 'Seau',
        ]);

        $this->artisan('dbs:fix')->assertExitCode(0);

        $this->assertSame('JU 1234', $avecPlaque->fresh()->numero);
        $this->assertSame('Remorque', $sansPlaque->fresh()->numero);
        $this->assertSame('V-07', $dejaNumerote->fresh()->numero);
        $this->assertSame('', $nonVehicule->fresh()->numero);
    }
}
