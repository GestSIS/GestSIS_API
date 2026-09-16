<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\ArticleBusiness;
use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Emplacement;
use App\Models\MaterielType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PeremptionArticleTest extends TestCase
{
    use DatabaseTransactions;

    private function creerTypePerimable(bool $perimable = true): MaterielType
    {
        return MaterielType::factory()->create(['est_perimable' => $perimable]);
    }

    public function testStatutsComptentLesArticlesDontLaDateDePeremptionEstAtteinte(): void
    {
        $type = $this->creerTypePerimable();

        Article::factory()->create([
            'materiel_type_id' => $type->id,
            'statut' => true,
            'date_peremption' => now()->subDay()->toDateString(),
        ]);
        Article::factory()->create([
            'materiel_type_id' => $type->id,
            'statut' => true,
            'date_peremption' => now()->toDateString(),
        ]);
        Article::factory()->create([
            'materiel_type_id' => $type->id,
            'statut' => true,
            'date_peremption' => now()->addMonth()->toDateString(),
        ]);

        $statuts = collect(MaterielTypeBusiness::calculerStatutsPeremption())->keyBy('materiel_type_id');

        // La date du jour compte comme périmée, la date future non.
        $this->assertSame(2, $statuts[$type->id]['nb_perimes']);
    }

    public function testArticleInactifOuSansDateNestPasComptePerime(): void
    {
        $type = $this->creerTypePerimable();

        Article::factory()->create([
            'materiel_type_id' => $type->id,
            'statut' => false,
            'date_peremption' => now()->subYear()->toDateString(),
        ]);
        Article::factory()->create([
            'materiel_type_id' => $type->id,
            'statut' => true,
            'date_peremption' => null,
        ]);

        $statuts = collect(MaterielTypeBusiness::calculerStatutsPeremption())->keyBy('materiel_type_id');

        $this->assertSame(0, $statuts[$type->id]['nb_perimes']);
    }

    public function testTypeNonPerimableEstIgnore(): void
    {
        $type = $this->creerTypePerimable(false);

        Article::factory()->create([
            'materiel_type_id' => $type->id,
            'statut' => true,
            'date_peremption' => now()->subYear()->toDateString(),
        ]);

        $statuts = collect(MaterielTypeBusiness::calculerStatutsPeremption())->keyBy('materiel_type_id');

        $this->assertArrayNotHasKey($type->id, $statuts->all());
    }

    public function testArticlesPerimesRenvoieLeDetailEtEcarteLesTypesSansArticlePerime(): void
    {
        $typeAvec = $this->creerTypePerimable();
        $typeSans = $this->creerTypePerimable();

        $perime = Article::factory()->create([
            'materiel_type_id' => $typeAvec->id,
            'statut' => true,
            'date_peremption' => now()->subMonth()->toDateString(),
        ]);
        Article::factory()->create([
            'materiel_type_id' => $typeSans->id,
            'statut' => true,
            'date_peremption' => now()->addYear()->toDateString(),
        ]);

        $resultat = collect(MaterielTypeBusiness::getArticlesPerimes())->keyBy('materiel_type_id');

        $this->assertArrayNotHasKey($typeSans->id, $resultat->all());
        $this->assertCount(1, $resultat[$typeAvec->id]['articles']);
        $this->assertSame($perime->id, $resultat[$typeAvec->id]['articles'][0]['id']);
        $this->assertSame(
            $perime->date_peremption->toDateString(),
            $resultat[$typeAvec->id]['articles'][0]['date_peremption'],
        );
    }

    public function testCreationRefuseeSansDateDePeremptionSurUnTypePerimable(): void
    {
        $type = $this->creerTypePerimable();
        $emplacement = Emplacement::factory()->create();

        $this->expectException(ArrayException::class);
        $this->expectExceptionMessage('nécessite une date de péremption');

        ArticleBusiness::creerArticles([[
            'materiel_type_id' => $type->id,
            'quantite' => 1,
            'sapeur_id' => null,
            'emplacement_id' => $emplacement->id,
            'date_peremption' => null,
        ]]);
    }

    public function testDateDePeremptionIgnoreeSurUnTypeNonPerimable(): void
    {
        $type = $this->creerTypePerimable(false);
        $emplacement = Emplacement::factory()->create();

        $articles = ArticleBusiness::creerArticles([[
            'materiel_type_id' => $type->id,
            'quantite' => 1,
            'sapeur_id' => null,
            'emplacement_id' => $emplacement->id,
            'date_peremption' => '2030-01-01',
        ]]);

        $this->assertNull(Article::findOrFail($articles[0]['id'])->date_peremption);
    }
}
