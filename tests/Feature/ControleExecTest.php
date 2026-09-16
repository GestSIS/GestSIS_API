<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\ControleExecBusiness;
use App\Models\Article;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Controle;
use App\Models\ControleMaterielType;
use App\Models\ControleTache;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ControleExecTest extends TestCase
{
    use DatabaseTransactions;

    private function creerControleAvecTache(string $type, array $tacheExtra = []): Controle
    {
        $controle = Controle::create([
            'nom' => 'Contrôle test',
            'recurrence_type' => 'PERIODIQUE',
            'recurrence_value' => 12,
        ]);

        ControleTache::create(array_merge([
            'controle_id' => $controle->id,
            'order' => 1,
            'nom' => 'Tâche test',
            'type' => $type,
        ], $tacheExtra));

        return $controle->load('taches');
    }

    /**
     * Un contrôle ne s'exécute que sur un article dont le type lui est
     * rattaché : les tests créent donc le type, le lie au contrôle, puis
     * l'article.
     */
    private function creerArticleRattache(Controle $controle, ?MaterielType $type = null): Article
    {
        $type ??= $this->creerTypeRattache($controle);

        return Article::factory()->create(['materiel_type_id' => $type->id]);
    }

    private function creerTypeRattache(Controle $controle): MaterielType
    {
        $type = MaterielType::factory()->create();

        ControleMaterielType::create([
            'controle_id' => $controle->id,
            'materiel_type_id' => $type->id,
        ]);

        return $type;
    }

    public function testKoSansRemarqueEstAccepteEtRendLeControleInvalide(): void
    {
        $controle = $this->creerControleAvecTache('BOOLEAN');
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        $exec = ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => now()->toDateString(),
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'date_echeance' => now()->addYear()->toDateString(),
            'taches' => [
                ['tache_id' => $controle->taches->first()->id, 'statut' => 'KO', 'remarque' => null],
            ],
        ], $sapeur->id);

        $this->assertFalse($exec->isConforme());
        $this->assertDatabaseHas('controle_exec_taches', [
            'controle_exec_id' => $exec->id,
            'statut' => 'KO',
            'remarque' => null,
        ]);
    }

    public function testValeurNumeriqueHorsPlageSansRemarqueEstAccepteeEtRendLeControleInvalide(): void
    {
        $controle = $this->creerControleAvecTache('NUMERIC', ['value_min' => 100, 'value_max' => 200]);
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        $exec = ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => now()->toDateString(),
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'date_echeance' => now()->addYear()->toDateString(),
            'taches' => [
                ['tache_id' => $controle->taches->first()->id, 'value_measured' => 50, 'remarque' => null],
            ],
        ], $sapeur->id);

        $this->assertFalse($exec->isConforme());
        $this->assertDatabaseHas('controle_exec_taches', [
            'controle_exec_id' => $exec->id,
            'value_measured' => 50,
            'value_in_range' => false,
        ]);
    }

    public function testToutesLesTachesOkRendLeControleValide(): void
    {
        $controle = $this->creerControleAvecTache('BOOLEAN');
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        $exec = ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => now()->toDateString(),
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'date_echeance' => now()->addYear()->toDateString(),
            'taches' => [
                ['tache_id' => $controle->taches->first()->id, 'statut' => 'OK', 'remarque' => null],
            ],
        ], $sapeur->id);

        $this->assertTrue($exec->isConforme());
    }

    public function testUpdateExecRemplaceLaDateLaRemarqueEtLesResultats(): void
    {
        $controle = $this->creerControleAvecTache('BOOLEAN');
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        $exec = ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => '2026-01-01',
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => 'avant',
            'date_echeance' => '2027-01-01',
            'taches' => [
                ['tache_id' => $controle->taches->first()->id, 'statut' => 'KO', 'remarque' => null],
            ],
        ], $sapeur->id);

        $exec = ControleExecBusiness::updateExec($exec->id, [
            'executed_at' => '2026-02-01',
            'remarque_globale' => 'après',
            'date_echeance' => '2027-02-01',
            'taches' => [
                ['tache_id' => $controle->taches->first()->id, 'statut' => 'OK', 'remarque' => null],
            ],
        ]);

        $this->assertSame('2026-02-01', $exec->executed_at->toDateString());
        $this->assertSame('après', $exec->remarque_globale);
        $this->assertTrue($exec->isConforme());
        $this->assertCount(1, $exec->execTaches);
        $this->assertDatabaseHas('controle_exec_taches', [
            'controle_exec_id' => $exec->id,
            'statut' => 'OK',
        ]);
    }

    public function testNbExecutionMaxAtteintRendLarticleEnEchecMemeSansTache(): void
    {
        $controle = Controle::create([
            'nom' => 'Lavage test',
            'recurrence_type' => 'NON_PERIODIQUE',
            'nb_execution_max' => 3,
            'nb_execution_preavis' => 2,
        ]);
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        for ($i = 0; $i < 2; $i++) {
            ControleExecBusiness::createExec($controle->id, $article->id, [
                'executed_at' => now()->toDateString(),
                'trigger_type' => 'NON_PERIODIQUE',
                'remarque_globale' => null,
                'taches' => [],
            ], $sapeur->id);
        }

        $lignes = ControleExecBusiness::getDernieresExecutionsParArticle($controle->id)->keyBy('article_id');
        $this->assertSame(2, $lignes[$article->id]->nb_executions);
        $this->assertFalse($lignes[$article->id]->dernier_controle_echec);

        ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => now()->toDateString(),
            'trigger_type' => 'NON_PERIODIQUE',
            'remarque_globale' => null,
            'taches' => [],
        ], $sapeur->id);

        $lignes = ControleExecBusiness::getDernieresExecutionsParArticle($controle->id)->keyBy('article_id');
        $this->assertSame(3, $lignes[$article->id]->nb_executions);
        $this->assertTrue($lignes[$article->id]->dernier_controle_echec);
    }

    public function testCreateExecsMultipleCreeUneExecutionParArticleAvecLaMemeDate(): void
    {
        $controle = $this->creerControleAvecTache('BOOLEAN');
        $type = $this->creerTypeRattache($controle);
        $articleA = $this->creerArticleRattache($controle, $type);
        $articleB = $this->creerArticleRattache($controle, $type);
        $sapeur = Sapeur::factory()->create();
        $tacheId = $controle->taches->first()->id;

        $execs = ControleExecBusiness::createExecsMultiple(
            $controle->id,
            '2026-03-01',
            'PERIODIQUE',
            'Contrôle groupé',
            [
                ['article_id' => $articleA->id, 'date_echeance' => '2028-03-01', 'taches' => [['tache_id' => $tacheId, 'statut' => 'OK', 'remarque' => null]]],
                ['article_id' => $articleB->id, 'date_echeance' => '2028-03-01', 'taches' => [['tache_id' => $tacheId, 'statut' => 'KO', 'remarque' => null]]],
            ],
            $sapeur->id,
        );

        $this->assertCount(2, $execs);
        $this->assertDatabaseHas('controle_execs', [
            'controle_id' => $controle->id,
            'article_id' => $articleA->id,
            'executed_at' => '2026-03-01 00:00:00',
            'remarque_globale' => 'Contrôle groupé',
        ]);
        $this->assertDatabaseHas('controle_execs', [
            'controle_id' => $controle->id,
            'article_id' => $articleB->id,
            'executed_at' => '2026-03-01 00:00:00',
        ]);
    }

    public function testCreateExecsMultipleEstToutOuRien(): void
    {
        $controle = $this->creerControleAvecTache('BOOLEAN');
        $type = $this->creerTypeRattache($controle);
        $articleValide = $this->creerArticleRattache($controle, $type);
        $articleInvalide = $this->creerArticleRattache($controle, $type);
        $sapeur = Sapeur::factory()->create();
        $tacheId = $controle->taches->first()->id;

        try {
            ControleExecBusiness::createExecsMultiple(
                $controle->id,
                '2026-03-01',
                'PERIODIQUE',
                null,
                [
                    ['article_id' => $articleValide->id, 'date_echeance' => '2028-03-01', 'taches' => [['tache_id' => $tacheId, 'statut' => 'OK', 'remarque' => null]]],
                    // Statut invalide pour cet article : doit faire échouer tout le lot.
                    ['article_id' => $articleInvalide->id, 'date_echeance' => '2028-03-01', 'taches' => [['tache_id' => $tacheId, 'statut' => 'INVALIDE', 'remarque' => null]]],
                ],
                $sapeur->id,
            );
            $this->fail('Une exception aurait dû être levée');
        } catch (\Throwable) {
            // attendu
        }

        $this->assertDatabaseMissing('controle_execs', ['article_id' => $articleValide->id]);
        $this->assertDatabaseMissing('controle_execs', ['article_id' => $articleInvalide->id]);
    }

    public function testExecRefuseeSiLeTypeDeLarticleNestPasRattacheAuControle(): void
    {
        $controle = $this->creerControleAvecTache('BOOLEAN');
        $this->creerTypeRattache($controle);
        $articleHorsPerimetre = Article::factory()->create([
            'materiel_type_id' => MaterielType::factory()->create()->id,
        ]);
        $sapeur = Sapeur::factory()->create();

        $this->expectException(ArrayException::class);

        ControleExecBusiness::createExec($controle->id, $articleHorsPerimetre->id, [
            'executed_at' => now()->toDateString(),
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'taches' => [
                ['tache_id' => $controle->taches->first()->id, 'statut' => 'OK', 'remarque' => null],
            ],
        ], $sapeur->id);
    }
}
