<?php

namespace Tests\Feature;

use App\Models\Controle;
use App\Models\ControleMaterielType;
use App\Models\ControleTache;
use App\Models\MaterielType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Les tâches et les types de matériel d'un contrôle sont des sous-ressources :
 * elles ne sont adressables qu'à travers leur contrôle parent.
 */
class ControleSousRessourceTest extends TestCase
{
    use DatabaseTransactions;

    private function creerControle(string $nom = 'Contrôle test'): Controle
    {
        return Controle::create([
            'nom' => $nom,
            'recurrence_type' => 'PERIODIQUE',
            'recurrence_value' => 12,
        ]);
    }

    public function testUpdateTacheDunAutreControleRenvoieUne404EtNeModifieRien(): void
    {
        $controle = $this->creerControle();
        $autreControle = $this->creerControle('Autre contrôle');

        $tache = ControleTache::create([
            'controle_id' => $autreControle->id,
            'order' => 1,
            'nom' => 'Tâche intacte',
            'type' => 'BOOLEAN',
        ]);

        $response = $this->json('PUT', "/api/v2/controles/{$controle->id}/taches/{$tache->id}", [
            'nom' => 'Tâche renommée',
            'order' => 1,
            'type' => 'BOOLEAN',
        ], ['Sis-Key' => 1]);

        $response->assertNotFound();
        $this->assertSame('Tâche intacte', $tache->fresh()->nom);
    }

    public function testDestroyTacheDunAutreControleRenvoieUne404EtNeSupprimeRien(): void
    {
        $controle = $this->creerControle();
        $autreControle = $this->creerControle('Autre contrôle');

        $tache = ControleTache::create([
            'controle_id' => $autreControle->id,
            'order' => 1,
            'nom' => 'Tâche intacte',
            'type' => 'BOOLEAN',
        ]);

        $response = $this->json('DELETE', "/api/v2/controles/{$controle->id}/taches/{$tache->id}", [], ['Sis-Key' => 1]);

        $response->assertNotFound();
        $this->assertDatabaseHas('controle_taches', ['id' => $tache->id]);
    }

    public function testUpdateTacheDuBonControleFonctionne(): void
    {
        $controle = $this->creerControle();

        $tache = ControleTache::create([
            'controle_id' => $controle->id,
            'order' => 1,
            'nom' => 'Avant',
            'type' => 'BOOLEAN',
        ]);

        $response = $this->json('PUT', "/api/v2/controles/{$controle->id}/taches/{$tache->id}", [
            'nom' => 'Après',
            'order' => 2,
            'type' => 'BOOLEAN',
        ], ['Sis-Key' => 1]);

        $response->assertOk();
        $this->assertSame('Après', $tache->fresh()->nom);
        $this->assertSame('Après', $response->json('data.nom'));
    }

    public function testRattacherDeuxFoisLeMemeTypeEstRefuse(): void
    {
        $controle = $this->creerControle();
        $type = MaterielType::factory()->create();

        ControleMaterielType::create([
            'controle_id' => $controle->id,
            'materiel_type_id' => $type->id,
        ]);

        $response = $this->json('POST', "/api/v2/controles/{$controle->id}/materiel-types", [
            'materiel_type_id' => $type->id,
        ], ['Sis-Key' => 1]);

        // Les erreurs métier sont renvoyées en 200 avec une clé "error".
        $response->assertOk();
        $response->assertJsonStructure(['error' => ['message']]);
        $this->assertSame(1, ControleMaterielType::where('controle_id', $controle->id)->count());
    }

    public function testDestroyAssociationDunAutreControleRenvoieUne404(): void
    {
        $controle = $this->creerControle();
        $autreControle = $this->creerControle('Autre contrôle');
        $type = MaterielType::factory()->create();

        $association = ControleMaterielType::create([
            'controle_id' => $autreControle->id,
            'materiel_type_id' => $type->id,
        ]);

        $response = $this->json('DELETE', "/api/v2/controles/{$controle->id}/materiel-types/{$association->id}", [], ['Sis-Key' => 1]);

        $response->assertNotFound();
        $this->assertDatabaseHas('controle_materiel_types', ['id' => $association->id]);
    }
}
