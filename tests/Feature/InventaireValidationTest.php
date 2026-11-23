<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\InventaireBusiness;
use App\Models\Inventaire;
use App\Models\InventaireEmplacement;
use App\Models\InventaireLigne;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class InventaireValidationTest extends TestCase
{
    use DatabaseTransactions;

    private Inventaire $inventaire;

    private InventaireEmplacement $racine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventaire = Inventaire::factory()->create();
        $this->racine = InventaireEmplacement::factory()->create(['inventaire_id' => $this->inventaire->id]);
    }

    public function testValiderTermineLInventaireEtEnregistreLeValideur(): void
    {
        InventaireLigne::factory()->for($this->racine, 'inventaireEmplacement')->present()->create();
        InventaireLigne::factory()->for($this->racine, 'inventaireEmplacement')->absent()->create();
        $sapeur = Sapeur::factory()->create();

        InventaireBusiness::valider($this->inventaire->id, '  Compartiment D1 humide  ', $sapeur->id);

        $this->inventaire->refresh();
        $this->assertSame('VALIDE', $this->inventaire->statut);
        $this->assertSame($sapeur->id, $this->inventaire->valide_par_sapeur_id);
        $this->assertNotNull($this->inventaire->valide_le);
        $this->assertSame('Compartiment D1 humide', $this->inventaire->remarque);
    }

    public function testValiderRenvoieLInventaireComplet(): void
    {
        InventaireLigne::factory()->for($this->racine, 'inventaireEmplacement')->present()->create();

        $this->valider()
            ->assertStatus(200)
            ->assertJsonPath('data.statut', 'VALIDE')
            ->assertJsonPath('data.emplacements.0.id', $this->racine->id)
            ->assertJsonCount(1, 'data.lignes');
    }

    public function testValiderAvecDesPiecesAVerifierEstRefuse(): void
    {
        InventaireLigne::factory()->for($this->racine, 'inventaireEmplacement')->present()->create();
        InventaireLigne::factory()->for($this->racine, 'inventaireEmplacement')->count(2)->create();

        $this->valider()
            ->assertStatus(422)
            ->assertJsonFragment(['message' => "Il reste 2 pièces à vérifier avant de valider l'inventaire"]);
        $this->assertSame('EN_COURS', $this->inventaire->refresh()->statut);
    }

    public function testValiderUnInventaireDejaValideEstRefuse(): void
    {
        $this->inventaire->update(['statut' => 'VALIDE']);

        $this->valider()->assertStatus(422);
    }

    public function testUnInventaireValideNAcceptePlusDeSaisie(): void
    {
        $ligne = InventaireLigne::factory()->for($this->racine, 'inventaireEmplacement')->present()->create(['numero' => 'LAN-04']);
        $this->valider()->assertStatus(200);

        $this->json('PUT', "/api/v2/inventaires/{$this->inventaire->id}/lignes/{$ligne->id}", ['statut' => 'ABSENT'], ['Sis-Key' => 1])
            ->assertStatus(422);
        $this->assertSame('PRESENT', $ligne->refresh()->statut);
    }

    public function testValiderUnInventaireInexistantRenvoie404(): void
    {
        $this->json('POST', '/api/v2/inventaires/999999999/valider', [], ['Sis-Key' => 1])->assertStatus(404);
    }

    private function valider(): TestResponse
    {
        return $this->json('POST', "/api/v2/inventaires/{$this->inventaire->id}/valider", [], ['Sis-Key' => 1]);
    }
}
