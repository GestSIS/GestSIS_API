<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleAttribution;
use App\Models\Sapeur;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ArticleAttributionModelTest extends TestCase
{
    use DatabaseTransactions;

    public function testUneSeuleAttributionOuverteParArticle(): void
    {
        $article = Article::factory()->create();
        ArticleAttribution::factory()->create(['article_id' => $article->id]);

        $this->expectException(QueryException::class);

        ArticleAttribution::factory()->create([
            'article_id' => $article->id,
            'sapeur_id' => Sapeur::factory(),
        ]);
    }

    public function testDeuxAttributionsClosesPourLeMemeArticleSontAcceptees(): void
    {
        $article = Article::factory()->create();

        ArticleAttribution::factory()->create([
            'article_id' => $article->id,
            'debut' => '2025-01-01',
            'fin' => '2025-06-30',
        ]);
        ArticleAttribution::factory()->create([
            'article_id' => $article->id,
            'debut' => '2025-07-01',
            'fin' => '2025-12-31',
        ]);

        $this->assertSame(2, $article->attributions()->count());
        $this->assertNull($article->attributionOuverte);
    }

    public function testScopeNonSupprimesExclutLesArticlesSupprimes(): void
    {
        $article = Article::factory()->create();
        $articleSupprime = Article::factory()->supprime()->create();

        $ids = Article::nonSupprimes()
            ->whereIn('id', [$article->id, $articleSupprime->id])
            ->pluck('id')
            ->all();

        $this->assertSame([$article->id], $ids);
    }
}
