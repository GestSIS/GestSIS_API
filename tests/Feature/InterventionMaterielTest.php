<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\InterventionMateriel;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InterventionMaterielTest extends TestCase
{
    use DatabaseTransactions;

    protected $interventionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->interventionId = $this->json('POST', '/api/v2/interventions', Intervention::factory()->make()->toArray())
            ->json('data.id');
    }

    /**
     * Test index interventions
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionIndexMaterielOk()
    {
        $response = $this->json('GET', "/api/v2/interventions/393/materiels");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'intervention_id',
                        'materiel_id'
                    ]
                ]
            ]);
    }

    /**
     * Test add presence
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionMateriels()
    {
        $materiels = InterventionMateriel::factory()->count(1)->make();

        $response = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/materiels', ['materiels' => $materiels]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Ajouter deux fois le même matériel renvoie une erreur propre, pas un crash
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionMaterielDuplicatRefuse()
    {
        $materiel = InterventionMateriel::factory()->make()->toArray();

        $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/materiels', ['materiels' => [$materiel]])
            ->assertStatus(200)
            ->assertJsonMissingPath('error');

        $response = $this->json(
            'POST',
            '/api/v2/interventions/' . $this->interventionId . '/materiels',
            ['materiels' => [['materiel_id' => $materiel['materiel_id'], 'quantite' => 99]]]
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('error.materiel_id', 'Matériel déjà présent');

        // La quantité d'origine n'a pas été écrasée et il n'y a qu'une seule ligne
        $lignes = InterventionMateriel::where('intervention_id', $this->interventionId)
            ->where('materiel_id', $materiel['materiel_id'])
            ->get();
        $this->assertCount(1, $lignes);
        $this->assertEquals($materiel['quantite'], $lignes[0]->quantite);
    }

    /**
     * Test edit presence
     *
     * @return void
     * @throws Exception
     */
    public function testEditInterventionMateriels()
    {
        $this->interventionId = $this->json('POST', '/api/v2/interventions', Intervention::factory()->make()->toArray())->json('data.id');

        $materiels = InterventionMateriel::factory()->count(1)->make(['intervention_id' => $this->interventionId])->toArray();

        $res = $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/materiels', ['materiels' => $materiels])->json('data');

        $response = $this->json('PUT', '/api/v2/interventions/' . $this->interventionId . '/materiels', ['materiels' => $res]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test remove presence
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveInterventionMateriels()
    {
        $this->interventionId = $this->json('POST', '/api/v2/interventions', Intervention::factory()->make()->toArray())->json('data.id');

        $materiels = InterventionMateriel::factory()->count(1)->make(['intervention_id' => $this->interventionId])->toArray();

        $ids = array_column(
            $this->json('POST', '/api/v2/interventions/' . $this->interventionId . '/materiels', ['materiels' => $materiels])->json('data'),
            'id'
        );
        $response = $this->json('DELETE', '/api/v2/interventions/' . $this->interventionId . '/materiels', ['materiels' => $ids]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
