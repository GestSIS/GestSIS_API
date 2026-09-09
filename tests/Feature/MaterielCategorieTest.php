<?php

namespace Tests\Feature;

use App\Models\MaterielCategorie;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MaterielCategorieTest extends TestCase
{
    use DatabaseTransactions;

    public function testEditCategorieRejectsDirectSelfParenting(): void
    {
        $categorie = MaterielCategorie::factory()->create();

        $response = $this->json('PUT', "/api/v2/materiel-categories/{$categorie->id}", [
            'designation' => $categorie->designation,
            'parent_id' => $categorie->id,
            'couleur_id' => $categorie->couleur_id,
        ]);

        $response->assertStatus(200)->assertJsonStructure(['error']);
        $this->assertNull($categorie->fresh()->parent_id);
    }

    public function testEditCategorieRejectsMultiLevelCycle(): void
    {
        // Hiérarchie : a (racine) -> b -> c. On tente de faire de "a" un enfant de "c",
        // ce qui créerait un cycle a -> b -> c -> a sans jamais réutiliser directement l'id de "a".
        $a = MaterielCategorie::factory()->create(['parent_id' => null]);
        $b = MaterielCategorie::factory()->create(['parent_id' => $a->id]);
        $c = MaterielCategorie::factory()->create(['parent_id' => $b->id]);

        $response = $this->json('PUT', "/api/v2/materiel-categories/{$a->id}", [
            'designation' => $a->designation,
            'parent_id' => $c->id,
            'couleur_id' => $a->couleur_id,
        ]);

        $response->assertStatus(200)->assertJsonStructure(['error']);
        $this->assertNull($a->fresh()->parent_id);
    }

    public function testEditCategorieSucceedsWithValidParentChange(): void
    {
        $ancienParent = MaterielCategorie::factory()->create();
        $nouveauParent = MaterielCategorie::factory()->create();
        $categorie = MaterielCategorie::factory()->create(['parent_id' => $ancienParent->id]);

        $response = $this->json('PUT', "/api/v2/materiel-categories/{$categorie->id}", [
            'designation' => $categorie->designation,
            'parent_id' => $nouveauParent->id,
            'couleur_id' => $categorie->couleur_id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($nouveauParent->id, $categorie->fresh()->parent_id);
    }
}
