<?php

namespace Tests\Feature;

use App\Models\Fonction;
use App\Models\FonctionSapeur;
use App\Models\Grade;
use App\Models\GradeSapeur;
use App\Models\Sapeur;
use App\Support\Sis;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * La commande bascule la connexion par défaut sur `db_<sis>` : les données du test
 * sont donc créées sur cette même connexion (clonage de `test` vers `test`), qui est
 * aussi annulée en fin de test.
 */
class CloneSapeurTest extends TestCase
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

    private function cloner(Sapeur $sapeur): Sapeur
    {
        $this->artisan('sapeur:clone', [
            'sapeur_id' => $sapeur->id,
            'source_sis' => self::SIS,
            'target_sis' => self::SIS,
        ])->assertExitCode(0);

        return Sapeur::where('id', '>', $sapeur->id)->where('no_avs', $sapeur->no_avs)->firstOrFail();
    }

    public function testCloneRecomputesFonctionAndGradeFromClonedRows(): void
    {
        [$fonctionObsolete, $fonctionActuelle] = Fonction::factory()->count(2)->create();
        $grade = Grade::factory()->create();
        $sapeur = Sapeur::factory()->create([
            'fonction_id' => $fonctionObsolete->id,
            'grade_id' => null,
        ]);
        FonctionSapeur::factory()->create([
            'sapeur_id' => $sapeur->id,
            'fonction_id' => $fonctionActuelle->id,
            'debut' => now()->subYear(),
            'fin' => null,
        ]);
        GradeSapeur::factory()->create([
            'sapeur_id' => $sapeur->id,
            'grade_id' => $grade->id,
            'date' => now()->subYear(),
        ]);

        $clone = $this->cloner($sapeur);

        $this->assertSame($fonctionActuelle->id, $clone->fonction_id);
        $this->assertSame($grade->id, $clone->grade_id);
    }

    public function testCloneDoesNotCopyFonctionAndGradeColumnsWithoutMatchingRows(): void
    {
        $fonction = Fonction::factory()->create();
        $grade = Grade::factory()->create();
        $sapeur = Sapeur::factory()->create([
            'fonction_id' => $fonction->id,
            'grade_id' => $grade->id,
        ]);

        $clone = $this->cloner($sapeur);

        $this->assertNull($clone->fonction_id);
        $this->assertNull($clone->grade_id);
    }
}
