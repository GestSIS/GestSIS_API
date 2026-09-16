<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\ControleBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleExec;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ControleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return array<string, mixed>
     */
    private function basePayload(array $extra = []): array
    {
        return array_merge([
            'nom' => 'Contrôle test',
            'description' => null,
            'recurrence_type' => 'NON_PERIODIQUE',
            'recurrence_value' => null,
            'duree_preavis' => null,
            'externe' => false,
            'reparateur' => null,
            'taches' => [],
            'materiel_type_ids' => [],
        ], $extra);
    }

    public function testNbExecutionMaxRefuseAvecRecurrencePeriodique(): void
    {
        $this->expectException(ArrayException::class);

        ControleBusiness::createControle($this->basePayload([
            'recurrence_type' => 'PERIODIQUE',
            'recurrence_value' => 12,
            'nb_execution_max' => 10,
        ]));
    }

    public function testPreavisSuperieurAuMaxEstRefuse(): void
    {
        $this->expectException(ArrayException::class);

        ControleBusiness::createControle($this->basePayload([
            'nb_execution_max' => 10,
            'nb_execution_preavis' => 11,
        ]));
    }

    public function testPreavisSansMaxEstRefuse(): void
    {
        $this->expectException(ArrayException::class);

        ControleBusiness::createControle($this->basePayload([
            'nb_execution_preavis' => 5,
        ]));
    }

    public function testCreeUnControleAvecNbExecutionMaxValide(): void
    {
        $controle = ControleBusiness::createControle($this->basePayload([
            'nb_execution_max' => 10,
            'nb_execution_preavis' => 8,
        ]));

        $this->assertSame(10, $controle->nb_execution_max);
        $this->assertSame(8, $controle->nb_execution_preavis);
    }

    public function testCalculerStatutsCompteLesArticlesAyantDepasseLeMax(): void
    {
        $type = MaterielType::factory()->create();
        $controle = ControleBusiness::createControle($this->basePayload([
            'nb_execution_max' => 3,
            'nb_execution_preavis' => 2,
            'materiel_type_ids' => [$type->id],
        ]));

        $articleEnRetard = Article::factory()->create(['materiel_type_id' => $type->id, 'statut' => true]);
        $articleEnPreavis = Article::factory()->create(['materiel_type_id' => $type->id, 'statut' => true]);
        $articleOk = Article::factory()->create(['materiel_type_id' => $type->id, 'statut' => true]);
        $sapeur = Sapeur::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            ControleExec::create([
                'controle_id' => $controle->id,
                'article_id' => $articleEnRetard->id,
                'executed_at' => now(),
                'executed_by' => $sapeur->id,
                'trigger_type' => 'NON_PERIODIQUE',
            ]);
        }
        for ($i = 0; $i < 2; $i++) {
            ControleExec::create([
                'controle_id' => $controle->id,
                'article_id' => $articleEnPreavis->id,
                'executed_at' => now(),
                'executed_by' => $sapeur->id,
                'trigger_type' => 'NON_PERIODIQUE',
            ]);
        }
        ControleExec::create([
            'controle_id' => $controle->id,
            'article_id' => $articleOk->id,
            'executed_at' => now(),
            'executed_by' => $sapeur->id,
            'trigger_type' => 'NON_PERIODIQUE',
        ]);

        $statuts = collect(ControleBusiness::calculerStatuts())->firstWhere('controle_id', $controle->id);

        $this->assertSame(1, $statuts['nb_en_retard']);
        $this->assertSame(1, $statuts['nb_en_preavis']);

        $articles = collect(ControleBusiness::getArticlesLimiteExecutions())->firstWhere('controle_id', $controle->id);
        $statutsParArticle = collect($articles['articles'])->keyBy('id');

        $this->assertSame('danger', $statutsParArticle[$articleEnRetard->id]['statut']);
        $this->assertSame('warning', $statutsParArticle[$articleEnPreavis->id]['statut']);
        $this->assertArrayNotHasKey($articleOk->id, $statutsParArticle);

        // Ce contrôle à compteur d'usage ne doit pas apparaître dans le widget
        // dédié aux échéances par date (PERIODIQUE) — on veut pouvoir distinguer
        // les deux dans le tableau de bord.
        $this->assertNull(
            collect(ControleBusiness::getArticlesAControler())->firstWhere('controle_id', $controle->id),
        );
    }

    public function testControlePeriodiqueEnRetardNApparaitPasDansLimiteExecutions(): void
    {
        $type = MaterielType::factory()->create();
        $controle = ControleBusiness::createControle($this->basePayload([
            'recurrence_type' => 'PERIODIQUE',
            'recurrence_value' => 1,
            'materiel_type_ids' => [$type->id],
        ]));
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'statut' => true]);
        $sapeur = Sapeur::factory()->create();

        ControleExec::create([
            'controle_id' => $controle->id,
            'article_id' => $article->id,
            'executed_at' => now()->subMonths(2),
            'executed_by' => $sapeur->id,
            'trigger_type' => 'PERIODIQUE',
        ]);

        $articles = collect(ControleBusiness::getArticlesAControler())->firstWhere('controle_id', $controle->id);
        $this->assertSame('danger', collect($articles['articles'])->keyBy('id')[$article->id]['statut']);

        // Symétriquement, un contrôle PERIODIQUE ne doit pas apparaître dans le
        // widget dédié aux compteurs d'usage.
        $this->assertNull(
            collect(ControleBusiness::getArticlesLimiteExecutions())->firstWhere('controle_id', $controle->id),
        );
    }
}
