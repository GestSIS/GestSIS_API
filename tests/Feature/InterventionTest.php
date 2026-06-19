<?php

namespace Tests\Feature;

use App\Models\ExerciceComptable;
use App\Models\Intervention;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InterventionTest extends TestCase
{
    use DatabaseTransactions;

    protected $interventionId;

    /**
     * Test index intervention
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionIndexOk()
    {
        $response = $this->json('GET', "/api/v2/interventions/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation'
                    ]
                ]
            ]);
    }

    /**
     * Test show intervention
     *
     * @return void
     * @throws Exception
     */
    public function testInterventionShowOk()
    {
        $intervention = Intervention::factory()->create();

        $response = $this->json('GET', "/api/v2/interventions/{$intervention->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'localite_id',
                    'date_debut'
                ]
            ]);
    }

    /**
     * Test add intervention
     *
     * @return void
     * @throws Exception
     */
    public function testAddInterventionOk()
    {
        $intervention = Intervention::factory()->make();
        $response = $this->json('POST', '/api/v2/interventions', $intervention->toArray());

        // dd($intervention);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test validate exercice
     *
     * @return void
     * @throws Exception
     */
    public function testValidateInterventionInvalid()
    {
        $intervention = Intervention::factory()->create();

        $response = $this->json('POST', "/api/v2/interventions/$intervention->id/valider");

        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => true
            ]);
    }

    /**
     * Test validate exercice
     *
     * @return void
     * @throws Exception
     */
    public function testValidateInterventionOk()
    {
        $intervention = Intervention::factory()->create();

        $sapeurs = array(
            array(
                'sapeur_id' => 1,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 2,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 3,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ),
        );

        $this->json('POST', "/api/v2/interventions/{$intervention->id}/sapeurs", ['sapeurs' => $sapeurs]);

        $response = $this->json('POST', "/api/v2/interventions/$intervention->id/valider");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test edit intervention
     *
     * @return void
     * @throws Exception
     */
    public function testEditIntervention()
    {
        $intervention = Intervention::factory()->create();
        $interventionEdited = Intervention::factory()->make();

        $response = $this->json(
            'PUT',
            '/api/v2/interventions/' . $intervention->id,
            $interventionEdited->toArray()
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test import complet intervention with a past year date_debut
     * where no ExerciceComptable exists for that year.
     * The system should auto-create one and succeed.
     *
     * @return void
     * @throws Exception
     */
    public function testImportInterventionCompletAutoCreatesExerciceComptableForPastYear()
    {
        $pastYear = now()->subYear()->year;

        // Ensure no ExerciceComptable exists for the past year
        ExerciceComptable::where('annee', $pastYear)->delete();

        $payload = [
            'date_debut' => "$pastYear-06-15",
            'heure_debut' => '10:00',
            'date_fin' => "$pastYear-06-15",
            'heure_fin' => '12:00',
            'objet' => 'Test past year intervention',
            'lieu' => 'Test lieu',
            'degre' => 2,
            'stat_nb' => 1,
            'sauve_personne' => 0,
            'sauve_animaux' => 0,
            'rapport_police' => false,
            'localite_id' => 1,
            'stat_federal_id' => 1,
            'sapeur_id' => 1,
            'type_intervention_id' => 1,
            'sapeurs' => [],
            'missions' => [],
            'appels' => [],
            'vehicules' => [],
            'groupes' => [],
            'quittances' => [],
            'materiel' => [],
        ];

        $response = $this->json('POST', '/api/v2/interventions-complet', $payload);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id']]);

        $this->assertDatabaseHas('exercice_comptables', ['annee' => $pastYear]);
    }
}
