<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\ControleBusiness;
use App\Domaine\Business\Materiel\ControleExecBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleMaterielType;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * L'échéance du prochain contrôle est désormais enregistrée sur chaque
 * exécution plutôt que recalculée depuis la récurrence du contrôle, ce qui
 * permet une récurrence variable d'un passage à l'autre (ex : premier service
 * véhicule à 5 ans, puis tous les 2 ans). Elle est obligatoire à la saisie
 * pour un contrôle PERIODIQUE — pas de calcul par défaut côté API, qui ne
 * ferait que dupliquer celui du front (voir addMonthsIso côté APP), seule
 * source de la valeur pré-remplie proposée à l'utilisateur.
 */
class ControleDateEcheanceTest extends TestCase
{
    use DatabaseTransactions;

    private function creerControlePeriodique(int $recurrenceValue = 24, ?int $dureePreavis = null): Controle
    {
        return Controle::create([
            'nom' => 'Service véhicule',
            'recurrence_type' => 'PERIODIQUE',
            'recurrence_value' => $recurrenceValue,
            'duree_preavis' => $dureePreavis,
        ]);
    }

    private function creerArticleRattache(Controle $controle): Article
    {
        $type = MaterielType::factory()->create();
        ControleMaterielType::create(['controle_id' => $controle->id, 'materiel_type_id' => $type->id]);

        return Article::factory()->create(['materiel_type_id' => $type->id, 'statut' => true]);
    }

    public function testCreateExecRefuseeSansDateEcheanceSurUnControlePeriodique(): void
    {
        $controle = $this->creerControlePeriodique(24);
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        $this->expectException(ArrayException::class);

        ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => '2026-01-15',
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'taches' => [],
        ], $sapeur->id);
    }

    public function testCreateExecAccepteEtRetientLaDateEcheanceSoumise(): void
    {
        // Cas cité : premier service à 5 ans au lieu des 2 ans habituels.
        $controle = $this->creerControlePeriodique(24);
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        $exec = ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => '2026-01-15',
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'date_echeance' => '2031-01-15',
            'taches' => [],
        ], $sapeur->id);

        $this->assertSame('2031-01-15', $exec->date_echeance->toDateString());
    }

    public function testDateEcheanceIgnoreeSurUnControleNonPeriodique(): void
    {
        $controle = Controle::create([
            'nom' => 'Lavage',
            'recurrence_type' => 'NON_PERIODIQUE',
        ]);
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        $exec = ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => '2026-01-15',
            'trigger_type' => 'NON_PERIODIQUE',
            'remarque_globale' => null,
            // Soumise malgré tout (ex : payload générique côté front) : doit être
            // ignorée, ce contrôle ne suit pas d'échéance par date, et n'exige donc
            // pas non plus sa présence.
            'date_echeance' => '2030-01-01',
            'taches' => [],
        ], $sapeur->id);

        $this->assertNull($exec->date_echeance);
    }

    public function testUpdateExecRefuseeSansDateEcheanceSurUnControlePeriodique(): void
    {
        $controle = $this->creerControlePeriodique(24);
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        $exec = ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => '2026-01-15',
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'date_echeance' => '2028-01-15',
            'taches' => [],
        ], $sapeur->id);

        $this->expectException(ArrayException::class);

        ControleExecBusiness::updateExec($exec->id, [
            'executed_at' => '2026-03-01',
            'remarque_globale' => null,
            'taches' => [],
        ]);
    }

    public function testUpdateExecRespecteLaNouvelleDateEcheanceSoumise(): void
    {
        $controle = $this->creerControlePeriodique(24);
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        $exec = ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => '2026-01-15',
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'date_echeance' => '2028-01-15',
            'taches' => [],
        ], $sapeur->id);

        $exec = ControleExecBusiness::updateExec($exec->id, [
            'executed_at' => '2026-01-15',
            'remarque_globale' => null,
            'date_echeance' => '2031-01-15',
            'taches' => [],
        ]);

        $this->assertSame('2031-01-15', $exec->date_echeance->toDateString());
    }

    public function testStatutSuitLecheanceStockeePasLaRecurrenceParDefaut(): void
    {
        // Contrôle à récurrence 24 mois, mais échéance overridée à une date déjà
        // dépassée : le statut doit suivre l'échéance stockée, pas recalculer
        // 24 mois après la dernière exécution.
        $controle = $this->creerControlePeriodique(24);
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => now()->subMonth()->toDateString(),
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'date_echeance' => now()->subDay()->toDateString(),
            'taches' => [],
        ], $sapeur->id);

        $articles = collect(ControleBusiness::getArticlesAControler())->firstWhere('controle_id', $controle->id);
        $ligne = collect($articles['articles'])->keyBy('id')[$article->id];

        $this->assertSame('danger', $ligne['statut']);
        $this->assertSame(now()->subDay()->toDateString(), $ligne['prochaine_execution']);
    }

    public function testGetDernieresExecutionsParArticleExposeDateEcheanceEtStatut(): void
    {
        $controle = $this->creerControlePeriodique(24, dureePreavis: 2);
        $article = $this->creerArticleRattache($controle);
        $sapeur = Sapeur::factory()->create();

        ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => now()->toDateString(),
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'date_echeance' => now()->addMonth()->toDateString(),
            'taches' => [],
        ], $sapeur->id);

        $ligne = ControleExecBusiness::getDernieresExecutionsParArticle($controle->id)->keyBy('article_id')[$article->id];

        $this->assertSame(now()->addMonth()->toDateString(), $ligne->date_echeance);
        // Échéance dans un mois, préavis de 2 mois : déjà en préavis.
        $this->assertSame('warning', $ligne->statut);
    }

    public function testStoreHttpRefuseUneExecutionPeriodiqueSansDateEcheance(): void
    {
        $controle = $this->creerControlePeriodique(24);
        $article = $this->creerArticleRattache($controle);

        $response = $this->json(
            'POST',
            "/api/v2/controles/{$controle->id}/articles/{$article->id}/execs",
            [
                'executed_at' => '2026-01-15',
                'trigger_type' => 'PERIODIQUE',
                'taches' => [],
            ],
            ['Sis-Key' => 1],
        );

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['date_echeance']]);
        $this->assertDatabaseMissing('controle_execs', ['controle_id' => $controle->id, 'article_id' => $article->id]);
    }
}
