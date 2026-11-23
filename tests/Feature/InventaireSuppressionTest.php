<?php

namespace Tests\Feature;

use App\Models\Inventaire;
use App\Models\InventaireEmplacement;
use App\Models\InventaireLigne;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class InventaireSuppressionTest extends TestCase
{
    use DatabaseTransactions;

    private Inventaire $inventaire;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventaire = Inventaire::factory()->create();
    }

    public function testSupprimerUnInventaireEnCoursEffaceSonPerimetreEtSaSaisie(): void
    {
        $racine = InventaireEmplacement::factory()->create(['inventaire_id' => $this->inventaire->id]);
        $enfant = InventaireEmplacement::factory()->enfantDe($racine)->create();
        $ligne = InventaireLigne::factory()->for($enfant, 'inventaireEmplacement')->present()->create();

        $this->supprimer()->assertStatus(204);

        $this->assertModelMissing($this->inventaire);
        $this->assertModelMissing($racine);
        $this->assertModelMissing($enfant);
        $this->assertModelMissing($ligne);
    }

    public function testSupprimerUnInventaireValideEstRefuse(): void
    {
        $this->inventaire->update(['statut' => 'VALIDE']);

        $this->supprimer()->assertStatus(422);
        $this->assertModelExists($this->inventaire);
    }

    private function supprimer(): TestResponse
    {
        return $this->json('DELETE', "/api/v2/inventaires/{$this->inventaire->id}", [], ['Sis-Key' => 1]);
    }
}
