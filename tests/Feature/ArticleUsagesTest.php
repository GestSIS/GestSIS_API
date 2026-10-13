<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleExec;
use App\Models\ControleMaterielType;
use App\Models\Emplacement;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ArticleUsagesTest extends TestCase
{
    use DatabaseTransactions;

    private function creerControle(string $type, array $extra = []): Controle
    {
        return Controle::create(array_merge([
            'nom' => 'Contrôle test',
            'recurrence_type' => $type,
        ], $extra));
    }

    private function rattacher(Controle $controle, MaterielType $type): void
    {
        ControleMaterielType::create([
            'controle_id' => $controle->id,
            'materiel_type_id' => $type->id,
        ]);
    }

    private function creerExecs(Controle $controle, Article $article, int $nb): void
    {
        $sapeur = Sapeur::factory()->create();

        for ($i = 0; $i < $nb; $i++) {
            ControleExec::create([
                'controle_id' => $controle->id,
                'article_id' => $article->id,
                'executed_at' => now(),
                'executed_by' => $sapeur->id,
                'trigger_type' => $controle->recurrence_type,
            ]);
        }
    }

    public function testUsagesCompteLesExecutionsDesControlesAUsageLimite(): void
    {
        $usage = $this->creerControle('NON_PERIODIQUE', ['nb_execution_max' => 50]);
        $periodique = $this->creerControle('PERIODIQUE', ['recurrence_value' => 12]);
        $type = MaterielType::factory()->create();
        $this->rattacher($usage, $type);
        $this->rattacher($periodique, $type);
        $article = Article::factory()->create(['materiel_type_id' => $type->id]);
        $this->creerExecs($usage, $article, 3);
        $this->creerExecs($periodique, $article, 1);

        $response = $this->json('GET', "/api/v2/materiel-types/{$type->id}/articles", [], ['Sis-Key' => 1]);

        $response->assertOk();
        $this->assertSame([(string) $usage->id => 3], $response->json('data.0.usages'));
        $this->assertArrayNotHasKey('lavages', $response->json('data.0'));
    }

    public function testUsagesVideSansExecution(): void
    {
        $type = MaterielType::factory()->create();
        Article::factory()->create(['materiel_type_id' => $type->id]);

        $response = $this->json('GET', "/api/v2/materiel-types/{$type->id}/articles", [], ['Sis-Key' => 1]);

        $response->assertOk();
        $response->assertJsonPath('data.0.usages', []);
        $this->assertStringContainsString('"usages":{}', $response->getContent());
    }

    public function testUsagesSurArticlesParEmplacement(): void
    {
        $usage = $this->creerControle('NON_PERIODIQUE', ['nb_execution_max' => 50]);
        $type = MaterielType::factory()->create();
        $this->rattacher($usage, $type);
        $emplacement = Emplacement::factory()->create();
        $article = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => $emplacement->id,
        ]);
        $vide = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => $emplacement->id,
        ]);
        $this->creerExecs($usage, $article, 2);

        $response = $this->json('GET', "/api/v2/emplacements/{$emplacement->id}/articles", [], ['Sis-Key' => 1]);

        $response->assertOk();
        $articles = collect($response->json('data'))->keyBy('id');
        $this->assertSame([(string) $usage->id => 2], $articles[$article->id]['usages']);
        $this->assertArrayNotHasKey('lavages', $articles[$article->id]);
        $this->assertStringContainsString('"usages":{}', $response->getContent());
        $this->assertSame([], $articles[$vide->id]['usages']);
    }
}
