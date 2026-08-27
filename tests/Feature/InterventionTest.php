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

        $sapeurs = [
            [
                'sapeur_id' => 1,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
            [
                'sapeur_id' => 2,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
            [
                'sapeur_id' => 3,
                'debut' => '2019-12-12 12:15',
                'fin' => '2019-12-12 12:30',
                'piquet' => 0
            ],
        ];

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

    /**
     * Test import complet intervention with groupes whose "no" is alphanumeric,
     * a numeric string or an integer. All must pass validation and be stored,
     * since group numbers are not necessarily pure integers (e.g. "91n").
     *
     * @return void
     * @throws Exception
     */
    public function testImportInterventionCompletAcceptsAlphanumericAndIntegerGroupeNo()
    {
        $year = now()->year;

        $payload = [
            'date_debut' => "$year-06-15",
            'heure_debut' => '10:00',
            'date_fin' => "$year-06-15",
            'heure_fin' => '12:00',
            'objet' => 'Test groupe no alphanumeric',
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
            'groupes' => [
                ['no' => '91n', 'designation' => '1er secours, FMO Nord'],
                ['no' => '90', 'designation' => 'Groupe EM SIS FMO'],
                ['no' => 42, 'designation' => 'Groupe entier'],
            ],
            'quittances' => [],
            'materiel' => [],
        ];

        $response = $this->json('POST', '/api/v2/interventions-complet', $payload);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id']]);

        $interventionId = $response->json('data.id');

        $this->assertDatabaseHas('groupe_intervention', [
            'intervention_id' => $interventionId,
            'no' => '91n',
        ]);
        $this->assertDatabaseHas('groupe_intervention', [
            'intervention_id' => $interventionId,
            'no' => '90',
        ]);
        $this->assertDatabaseHas('groupe_intervention', [
            'intervention_id' => $interventionId,
            'no' => '42',
        ]);
    }

    /**
     * Test import complet intervention rejects a groupe "no" longer than
     * the 10-character database column.
     *
     * @return void
     * @throws Exception
     */
    public function testImportInterventionCompletRejectsTooLongGroupeNo()
    {
        $year = now()->year;

        $payload = [
            'date_debut' => "$year-06-15",
            'heure_debut' => '10:00',
            'date_fin' => "$year-06-15",
            'heure_fin' => '12:00',
            'objet' => 'Test groupe no too long',
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
            'groupes' => [
                ['no' => '12345678901', 'designation' => 'Trop long'],
            ],
            'quittances' => [],
            'materiel' => [],
        ];

        $response = $this->json('POST', '/api/v2/interventions-complet', $payload);

        // This API returns validation errors as HTTP 200 with an "error" payload
        // (see bootstrap/app.php withExceptions), not the default 422.
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['error' => ['groupes.0.no']]);

        $this->assertDatabaseMissing('groupe_intervention', ['no' => '12345678901']);
    }

    /**
     * Test import complet intervention defaults a null sapeur "debut" to the
     * intervention's start (date_debut + heure_debut), rounded down to the
     * nearest quarter hour, instead of rejecting the export.
     *
     * @return void
     * @throws Exception
     */
    public function testImportInterventionCompletDefaultsNullSapeurDebutToRoundedInterventionStart()
    {
        $year = now()->year;

        $payload = [
            'date_debut' => "$year-06-15",
            'heure_debut' => '10:07',
            'date_fin' => "$year-06-15",
            'heure_fin' => '12:00',
            'objet' => 'Test sapeur debut null',
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
            'sapeurs' => [
                [
                    'sapeur_id' => 1,
                    'debut' => null,
                    'fin' => "$year-06-15 11:00",
                    'piquet' => false,
                ],
            ],
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

        $interventionId = $response->json('data.id');

        $this->assertDatabaseHas('intervention_sapeur', [
            'intervention_id' => $interventionId,
            'sapeur_id' => 1,
            'debut' => "$year-06-15 10:00:00",
        ]);
    }
}
