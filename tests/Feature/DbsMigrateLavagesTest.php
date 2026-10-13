<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleExec;
use App\Models\ControleMaterielType;
use App\Models\Lavage;
use App\Models\MaterielType;
use App\Models\Sapeur;
use App\Support\Sis;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DbsMigrateLavagesTest extends TestCase
{
    use DatabaseTransactions;

    private const SIS = 'test';

    private const DESCRIPTION = "Créé automatiquement depuis l'ancien suivi des lavages";

    private const CONFIRMATION_SANS_LAVAGE = 'Créer le contrôle « Lavage » (aucun lavage à reprendre) ?';

    private const QUESTION = 'Sapeur à qui attribuer les lavages repris (sapeur_id)';

    protected $connectionsToTransact = ['testing', 'db_' . self::SIS];

    protected function setUp(): void
    {
        parent::setUp();
        Sis::use(self::SIS);
        // La base de test contient des types lavables issus des seeders : on les neutralise (annulé en fin de test).
        MaterielType::query()->update(['est_lavable' => false]);
    }

    protected function tearDown(): void
    {
        Config::set('database.default', 'testing');
        parent::tearDown();
    }

    private function confirmation(Sapeur $sapeur): string
    {
        return "Attribuer les lavages à {$sapeur->prenom} {$sapeur->nom} (#{$sapeur->id}) et lancer la migration ?";
    }

    private function migrer(Sapeur $sapeur): void
    {
        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS]])
            ->expectsQuestion(self::QUESTION, (string) $sapeur->id)
            ->expectsConfirmation($this->confirmation($sapeur), 'yes')
            ->assertExitCode(0);
    }

    private function articleLavable(): Article
    {
        $type = MaterielType::factory()->create(['est_lavable' => true]);

        return Article::factory()->create(['materiel_type_id' => $type->id]);
    }

    public function testMigrationNominale(): void
    {
        $sapeur = Sapeur::factory()->create();
        $typeA = MaterielType::factory()->create(['est_lavable' => true]);
        $typeB = MaterielType::factory()->create(['est_lavable' => false]);
        $typeC = MaterielType::factory()->create(['est_lavable' => true]);
        $a1 = Article::factory()->create(['materiel_type_id' => $typeA->id]);
        $a2 = Article::factory()->create(['materiel_type_id' => $typeA->id]);
        $b1 = Article::factory()->create(['materiel_type_id' => $typeB->id]);
        $l1 = Lavage::create(['article_id' => $a1->id, 'date' => '2025-03-10']);
        $l2 = Lavage::create(['article_id' => $a1->id, 'date' => '2025-06-11']);
        $l3 = Lavage::create(['article_id' => $a2->id, 'date' => '2025-07-12']);
        $l4 = Lavage::create(['article_id' => $b1->id, 'date' => '2025-08-13']);
        $controlesAvant = Controle::count();

        $this->migrer($sapeur);

        $this->assertSame($controlesAvant + 1, Controle::count());
        $controle = Controle::where('nom', 'Lavage')->latest('id')->firstOrFail();
        $this->assertSame("Créé automatiquement depuis l'ancien suivi des lavages", $controle->description);
        $this->assertSame('NON_PERIODIQUE', $controle->recurrence_type);
        $this->assertNull($controle->recurrence_value);
        $this->assertSame(50, $controle->nb_execution_max);
        $this->assertSame(40, $controle->nb_execution_preavis);
        $this->assertFalse($controle->externe);
        $this->assertNull($controle->reparateur);
        $this->assertSame(0, $controle->taches()->count());

        $this->assertEqualsCanonicalizing(
            [$typeA->id, $typeB->id, $typeC->id],
            ControleMaterielType::where('controle_id', $controle->id)->pluck('materiel_type_id')->all()
        );

        $this->assertSame(4, ControleExec::where('controle_id', $controle->id)->count());
        foreach ([$l1, $l2, $l3, $l4] as $lavage) {
            $lavage->refresh();
            $exec = ControleExec::where('controle_id', $controle->id)
                ->where('article_id', $lavage->article_id)
                ->where('executed_at', $lavage->date->format('Y-m-d') . ' 00:00:00')
                ->firstOrFail();
            $this->assertSame($sapeur->id, $exec->executed_by);
            $this->assertSame('NON_PERIODIQUE', $exec->trigger_type);
            $this->assertNull($exec->date_echeance);
            $this->assertNull($exec->remarque_globale);
            $this->assertEquals($lavage->created_at, $exec->created_at);
        }
        $this->assertSame(4, Lavage::count());
    }

    public function testRelanceDetecteDejaMigre(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleLavable();
        Lavage::create(['article_id' => $article->id, 'date' => '2025-03-10']);
        $this->migrer($sapeur);
        $controles = Controle::count();
        $execs = ControleExec::count();

        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS]])
            ->expectsOutputToContain('déjà migré')
            ->assertExitCode(0);

        $this->assertSame($controles, Controle::count());
        $this->assertSame($execs, ControleExec::count());
    }

    public function testRelanceDetecteLavageSansCreatedAt(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleLavable();
        DB::table('lavages')->insert(['article_id' => $article->id, 'date' => '2024-01-05', 'created_at' => null, 'updated_at' => null]);
        $this->migrer($sapeur);
        $this->assertSame(1, ControleExec::where('article_id', $article->id)->count());

        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS]])
            ->expectsOutputToContain('déjà migré')
            ->assertExitCode(0);

        $this->assertSame(1, ControleExec::where('article_id', $article->id)->count());
    }

    public function testDeuxLavagesMemeJourDonnentDeuxExecutions(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleLavable();
        Lavage::create(['article_id' => $article->id, 'date' => '2025-03-10']);
        Lavage::create(['article_id' => $article->id, 'date' => '2025-03-10']);

        $this->migrer($sapeur);

        $this->assertSame(2, ControleExec::where('article_id', $article->id)->count());
    }

    public function testRefusConfirmationNeModifieRien(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleLavable();
        Lavage::create(['article_id' => $article->id, 'date' => '2025-03-10']);
        $controles = Controle::count();
        $execs = ControleExec::count();

        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS]])
            ->expectsQuestion(self::QUESTION, (string) $sapeur->id)
            ->expectsConfirmation($this->confirmation($sapeur), 'no')
            ->assertExitCode(0);

        $this->assertSame($controles, Controle::count());
        $this->assertSame($execs, ControleExec::count());
    }

    public function testSapeurInexistantEstRedemande(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleLavable();
        Lavage::create(['article_id' => $article->id, 'date' => '2025-03-10']);

        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS]])
            ->expectsQuestion(self::QUESTION, 'abc')
            ->expectsQuestion(self::QUESTION, '999999')
            ->expectsQuestion(self::QUESTION, (string) $sapeur->id)
            ->expectsConfirmation($this->confirmation($sapeur), 'yes')
            ->assertExitCode(0);

        $this->assertSame(1, ControleExec::where('article_id', $article->id)->count());
    }

    public function testRienAMigrer(): void
    {
        $controles = Controle::count();

        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS]])
            ->expectsOutputToContain('rien à migrer')
            ->assertExitCode(0);

        $this->assertSame($controles, Controle::count());
    }

    public function testRelanceSansLavageAvecTypeLavable(): void
    {
        MaterielType::factory()->create(['est_lavable' => true]);

        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS]])
            ->expectsConfirmation(self::CONFIRMATION_SANS_LAVAGE, 'yes')
            ->assertExitCode(0);
        $this->assertSame(1, Controle::where('description', self::DESCRIPTION)->count());
        $execs = ControleExec::count();

        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS]])
            ->expectsOutputToContain('déjà migré')
            ->assertExitCode(0);

        $this->assertSame(1, Controle::where('description', self::DESCRIPTION)->count());
        $this->assertSame($execs, ControleExec::count());
    }

    public function testSansLavageNeDemandePasDeSapeur(): void
    {
        $type = MaterielType::factory()->create(['est_lavable' => true]);

        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS]])
            ->expectsConfirmation(self::CONFIRMATION_SANS_LAVAGE, 'yes')
            ->assertExitCode(0);

        $controle = Controle::where('description', self::DESCRIPTION)->firstOrFail();
        $this->assertSame(0, ControleExec::where('controle_id', $controle->id)->count());
        $this->assertSame([$type->id], ControleMaterielType::where('controle_id', $controle->id)->pluck('materiel_type_id')->all());
    }

    public function testNonInteractifEchoue(): void
    {
        $article = $this->articleLavable();
        Lavage::create(['article_id' => $article->id, 'date' => '2025-03-10']);
        $controles = Controle::count();

        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS], '--no-interaction' => true])
            ->assertFailed();

        $this->assertSame($controles, Controle::count());
    }

    public function testSisInconnuEchoue(): void
    {
        $this->artisan('dbs:migrate-lavages', ['--sis' => ['inconnu']])
            ->assertFailed();
    }

    public function testAvertitSiControleLavageExisteDeja(): void
    {
        $sapeur = Sapeur::factory()->create();
        $article = $this->articleLavable();
        Lavage::create(['article_id' => $article->id, 'date' => '2025-03-10']);
        Controle::create(['nom' => 'Lavage', 'recurrence_type' => 'NON_PERIODIQUE']);
        $avant = Controle::where('nom', 'Lavage')->count();

        $this->artisan('dbs:migrate-lavages', ['--sis' => [self::SIS]])
            ->expectsOutputToContain('existe déjà')
            ->expectsQuestion(self::QUESTION, (string) $sapeur->id)
            ->expectsConfirmation($this->confirmation($sapeur), 'yes')
            ->assertExitCode(0);

        $this->assertSame($avant + 1, Controle::where('nom', 'Lavage')->count());
    }
}
