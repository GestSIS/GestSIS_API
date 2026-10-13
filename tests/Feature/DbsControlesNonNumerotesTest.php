<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleMaterielType;
use App\Models\MaterielType;
use App\Support\Sis;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * La commande bascule la connexion par défaut sur `db_<sis>` : les données du
 * test sont créées sur `db_test`, aussi annulée en fin de test (cf. CloneSapeurTest).
 */
class DbsControlesNonNumerotesTest extends TestCase
{
    use DatabaseTransactions;

    private const SIS = 'test';

    protected $connectionsToTransact = ['testing', 'db_' . self::SIS];

    protected function setUp(): void
    {
        parent::setUp();
        Sis::use(self::SIS);
        // Isole le test des associations déjà présentes dans la base (seed).
        ControleMaterielType::query()->delete();
    }

    protected function tearDown(): void
    {
        Config::set('database.default', 'testing');
        parent::tearDown();
    }

    private function rattacher(string $nomControle, MaterielType $type): void
    {
        $controle = Controle::create(['nom' => $nomControle, 'recurrence_type' => 'NON_PERIODIQUE']);
        ControleMaterielType::create(['controle_id' => $controle->id, 'materiel_type_id' => $type->id]);
    }

    public function testListeLesTypesNonNumerotesEtLesArticlesSansNumero(): void
    {
        $nonNumerote = MaterielType::factory()->create(['designation' => 'Bottes test', 'est_numerote' => false]);
        Article::factory()->count(2)->create(['materiel_type_id' => $nonNumerote->id, 'numero' => '']);
        $this->rattacher('Entretien bottes test', $nonNumerote);

        $numeroteIncomplet = MaterielType::factory()->numerote()->create(['designation' => 'Veste test']);
        Article::factory()->create(['materiel_type_id' => $numeroteIncomplet->id, 'numero' => 'V-1']);
        Article::factory()->create(['materiel_type_id' => $numeroteIncomplet->id, 'numero' => ' ']);
        $this->rattacher('Contrôle veste test', $numeroteIncomplet);

        $numeroteComplet = MaterielType::factory()->numerote()->create(['designation' => 'Casque test']);
        Article::factory()->create(['materiel_type_id' => $numeroteComplet->id, 'numero' => 'C-1']);
        $this->rattacher('Contrôle casque test', $numeroteComplet);

        $this->artisan('dbs:controles-non-numerotes', ['--sis' => [self::SIS]])
            ->expectsTable(['Contrôle', 'Type de matériel', 'Problème', 'Articles concernés'], [
                ['Contrôle veste test', 'Veste test', 'articles sans numéro', 1],
                ['Entretien bottes test', 'Bottes test', 'type non numéroté', 2],
            ])
            ->assertExitCode(0);
    }

    public function testAucunControleConcerne(): void
    {
        $type = MaterielType::factory()->numerote()->create();
        Article::factory()->create(['materiel_type_id' => $type->id, 'numero' => 'A-1']);
        $this->rattacher('Contrôle test', $type);

        $this->artisan('dbs:controles-non-numerotes', ['--sis' => [self::SIS]])
            ->expectsOutputToContain('SIS test : aucun contrôle concerné')
            ->assertExitCode(0);
    }

    public function testSisInconnuEchoue(): void
    {
        $this->artisan('dbs:controles-non-numerotes', ['--sis' => ['inconnu']])
            ->assertExitCode(1);
    }
}
