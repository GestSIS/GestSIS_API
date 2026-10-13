<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Emplacement;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * La base de test peut déjà contenir des articles sans numéro (seed) : les
 * assertions portent uniquement sur les types créés par chaque test.
 */
class ArticlesSansNumeroTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function articlesSansNumeroParType()
    {
        $response = $this->json('GET', '/api/v2/materiel-types/articles-sans-numero', [], ['Sis-Key' => 1]);
        $response->assertStatus(200);

        return collect($response->json('data'))->keyBy('materiel_type_id');
    }

    public function testListeLesArticlesActifsSansNumeroGroupesParType(): void
    {
        $type = MaterielType::factory()->numerote()->create(['designation' => 'Veste test']);
        $emplacement = Emplacement::factory()->create(['designation' => 'Hangar test']);
        $sapeur = Sapeur::factory()->create(['nom' => 'Dupont', 'prenom' => 'Jean']);
        $sansNumero = Article::factory()->create([
            'materiel_type_id' => $type->id, 'numero' => '', 'statut' => true, 'emplacement_id' => $emplacement->id,
        ]);
        $espaces = Article::factory()->create([
            'materiel_type_id' => $type->id, 'numero' => '   ', 'statut' => true, 'sapeur_id' => $sapeur->id,
        ]);
        Article::factory()->create(['materiel_type_id' => $type->id, 'numero' => 'V-1', 'statut' => true]);
        Article::factory()->create(['materiel_type_id' => $type->id, 'numero' => '10', 'statut' => true]);

        $resultat = $this->articlesSansNumeroParType();

        $this->assertSame('Veste test', $resultat[$type->id]['designation']);
        $articles = collect($resultat[$type->id]['articles'])->keyBy('id');
        $this->assertEqualsCanonicalizing([$sansNumero->id, $espaces->id], $articles->keys()->all());
        $this->assertSame('Hangar test', $articles[$sansNumero->id]['localisation']);
        $this->assertSame('Dupont Jean', $articles[$espaces->id]['localisation']);
        // Article complet, pour pouvoir l'ouvrir dans la modale de modification.
        $this->assertSame($type->id, $articles[$sansNumero->id]['materiel_type_id']);
        $this->assertArrayHasKey('emplacement_representee', $articles[$sansNumero->id]);
    }

    public function testZeroEtTiretComptentCommeNumeroManquant(): void
    {
        $type = MaterielType::factory()->numerote()->create();
        $zero = Article::factory()->create(['materiel_type_id' => $type->id, 'numero' => '0', 'statut' => true]);
        $tiret = Article::factory()->create(['materiel_type_id' => $type->id, 'numero' => ' - ', 'statut' => true]);

        $ids = collect($this->articlesSansNumeroParType()[$type->id]['articles'])->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$zero->id, $tiret->id], $ids);
    }

    public function testIgnoreLesArticlesInactifsEtLesTypesNonNumerotes(): void
    {
        $typeNumerote = MaterielType::factory()->numerote()->create();
        Article::factory()->create(['materiel_type_id' => $typeNumerote->id, 'numero' => '', 'statut' => false]);
        $typeNonNumerote = MaterielType::factory()->create(['est_numerote' => false]);
        Article::factory()->create(['materiel_type_id' => $typeNonNumerote->id, 'numero' => '', 'statut' => true]);

        $resultat = $this->articlesSansNumeroParType();

        $this->assertArrayNotHasKey($typeNumerote->id, $resultat->all());
        $this->assertArrayNotHasKey($typeNonNumerote->id, $resultat->all());
    }

    public function testTypeDontTousLesArticlesSontNumerotesNApparaitPas(): void
    {
        $type = MaterielType::factory()->numerote()->create();
        Article::factory()->create(['materiel_type_id' => $type->id, 'numero' => 'A-1', 'statut' => true]);

        $this->assertArrayNotHasKey($type->id, $this->articlesSansNumeroParType()->all());
    }
}
