<?php

namespace Tests\Feature;

use App\Models\Emplacement;
use App\Models\Inventaire;
use App\Models\InventaireEmplacement;
use App\Models\InventaireLigne;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InventaireConsultationTest extends TestCase
{
    use DatabaseTransactions;

    public function testListeRenvoieLesInventairesAvecLeurRacineEtLeursCompteurs(): void
    {
        $emplacement = Emplacement::factory()->create(['designation' => 'Remorque ARI']);
        $inventaire = Inventaire::factory()->create(['designation' => 'Inventaire remorque']);
        $racine = InventaireEmplacement::factory()->create([
            'inventaire_id' => $inventaire->id,
            'emplacement_id' => $emplacement->id,
            'designation' => 'Remorque ARI',
        ]);
        $enfant = InventaireEmplacement::factory()->enfantDe($racine)->create();
        InventaireLigne::factory()->for($racine, 'inventaireEmplacement')->present()->create();
        InventaireLigne::factory()->for($enfant, 'inventaireEmplacement')->create();
        InventaireLigne::factory()->for($enfant, 'inventaireEmplacement')->absent()->create();
        InventaireLigne::factory()->for($enfant, 'inventaireEmplacement')->enPlus()->create(['decision' => 'AJOUTER_AU_STOCK']);

        $response = $this->json('GET', '/api/v2/inventaires', [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $ligne = collect($response->json('data'))->firstWhere('id', $inventaire->id);
        $this->assertSame('Inventaire remorque', $ligne['designation']);
        $this->assertSame('EN_COURS', $ligne['statut']);
        $this->assertSame($emplacement->id, $ligne['racine']['emplacement_id']);
        $this->assertSame('Remorque ARI', $ligne['racine']['designation']);
        $this->assertSame(4, $ligne['lignes_count']);
        $this->assertSame(3, $ligne['lignes_verifiees_count']);
        $this->assertSame(2, $ligne['ecarts_count']);
        $this->assertSame(1, $ligne['ecarts_a_traiter_count']);
    }

    public function testListeTrieLesInventairesDuPlusRecentAuPlusAncien(): void
    {
        $ancien = Inventaire::factory()->create(['date' => '2025-03-01']);
        $recent = Inventaire::factory()->create(['date' => '2026-03-01']);

        $ids = collect($this->json('GET', '/api/v2/inventaires', [], ['Sis-Key' => 1])->json('data'))
            ->pluck('id')
            ->filter(fn (int $id): bool => in_array($id, [$ancien->id, $recent->id], true))
            ->values()
            ->all();

        $this->assertSame([$recent->id, $ancien->id], $ids);
    }

    public function testDetailRenvoieLePerimetreEtLesLignes(): void
    {
        $inventaire = Inventaire::factory()->create();
        $racine = InventaireEmplacement::factory()->create(['inventaire_id' => $inventaire->id]);
        $enfant = InventaireEmplacement::factory()->enfantDe($racine)->create();
        $ligne = InventaireLigne::factory()->for($enfant, 'inventaireEmplacement')->absent()->create(['numero' => 'RAC-01']);

        $response = $this->json('GET', "/api/v2/inventaires/{$inventaire->id}", [], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $inventaire->id);
        $this->assertEqualsCanonicalizing([$racine->id, $enfant->id], collect($response->json('data.emplacements'))->pluck('id')->all());
        $this->assertSame($racine->id, collect($response->json('data.emplacements'))->firstWhere('id', $enfant->id)['parent_id']);
        $response->assertJsonPath('data.lignes.0.id', $ligne->id);
        $response->assertJsonPath('data.lignes.0.inventaire_emplacement_id', $enfant->id);
        $response->assertJsonPath('data.lignes.0.statut', 'ABSENT');
        $response->assertJsonPath('data.lignes.0.numero', 'RAC-01');
    }

    public function testChaqueLigneNonNumeroteePorteLaCleDeSonLot(): void
    {
        $inventaire = Inventaire::factory()->create();
        $noeud = InventaireEmplacement::factory()->create(['inventaire_id' => $inventaire->id]);
        $piece = fn (array $attributes) => InventaireLigne::factory()->for($noeud, 'inventaireEmplacement')
            ->create(['numero' => null, 'designation' => 'Pansement', ...$attributes]);
        $mars = $piece(['date_peremption' => '2027-03-01']);
        $memeLot = $piece(['date_peremption' => '2027-03-01']);
        $juin = $piece(['date_peremption' => '2027-06-01']);
        $numerotee = $piece(['numero' => 'LAM-01']);

        $lignes = collect($this->json('GET', "/api/v2/inventaires/{$inventaire->id}", [], ['Sis-Key' => 1])
            ->assertStatus(200)
            ->json('data.lignes'))->keyBy('id');

        $this->assertSame($lignes[$mars->id]['lot'], $lignes[$memeLot->id]['lot']);
        $this->assertNotSame($lignes[$mars->id]['lot'], $lignes[$juin->id]['lot']);
        $this->assertNull($lignes[$numerotee->id]['lot']);
    }

    public function testDetailDUnInventaireInexistantRenvoie404(): void
    {
        $this->json('GET', '/api/v2/inventaires/999999999', [], ['Sis-Key' => 1])->assertStatus(404);
    }
}
