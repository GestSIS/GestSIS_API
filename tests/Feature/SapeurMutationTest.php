<?php

namespace Tests\Feature;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\Business\TravauxParamBusiness;
use App\Models\Ecriture;
use App\Models\ExerciceSapeur;
use App\Models\Intervention;
use App\Models\InterventionSapeur;
use App\Models\Localite;
use App\Models\Mutation;
use App\Models\Sapeur;
use App\Models\Travail;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurMutationTest extends TestCase
{
    use DatabaseTransactions;

    protected $localiteId;
    protected $sapeurId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create localite fixture
        $localite = Localite::firstOrCreate(
            ['id' => 1],
            [
                'commune_id' => null,
                'npa' => '2800',
                'designation' => 'Test Localité',
            ]
        );

        $this->localiteId = $localite->id;

        // Create sapeur fixture for mutation tests
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $this->sapeurId = $sapeur->id;
    }

    public function testIndexMutationsReturnsListOfMutations()
    {
        $response = $this->json('GET', "/api/v2/sapeurs/{$this->sapeurId}/mutations");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'incorporation',
                        'sapeur_id',
                        'sortie',
                        'motif'
                    ]
                ]
            ]);

        // Verify the factory-created mutation is present
        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($responseData));
    }

    public function testIndexMutationsReturnsErrorWhenSapeurNotFound()
    {
        $response = $this->json('GET', '/api/v2/sapeurs/999999/mutations');

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function testAddMutationSuccessfully()
    {
        // Use dates BEFORE the initial mutation (2019-01-29) to avoid overlap
        $data = [
            'incorporation' => "2005-01-01",
            'sortie' => "2008-12-31",
            'motif' => 'Test mutation',
            'localite_id' => $this->localiteId
        ];

        $response = $this->json('POST', "/api/v2/sapeurs/{$this->sapeurId}/mutations", $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'mutation' => ['id', 'incorporation', 'sapeur_id', 'sortie', 'motif'],
                    'statut_sapeur'
                ]
            ])
            ->assertJsonPath('data.mutation.sapeur_id', $this->sapeurId)
            ->assertJsonPath('data.mutation.motif', $data['motif'])
            ->assertJsonPath('data.mutation.localite_id', $data['localite_id']);

        // Verify incorporation date (JSON returns ISO format)
        $mutationIncorporation = Carbon::parse($response->json('data.mutation.incorporation'));
        $expectedIncorporation = Carbon::parse($data['incorporation']);
        $this->assertTrue($mutationIncorporation->isSameDay($expectedIncorporation));

        // Verify the mutation exists via GET
        $mutationId = $response->json('data.mutation.id');
        $getResponse = $this->json('GET', "/api/v2/sapeurs/{$this->sapeurId}/mutations");
        $mutations = $getResponse->json('data');
        $this->assertContains($mutationId, array_column($mutations, 'id'));
    }

    public function testAddMutationWithoutMotif()
    {
        // Le motif est facultatif : absent ou null, il est enregistré vide
        foreach ([['2001-01-01', '2001-12-31', []], ['2003-01-01', '2003-12-31', ['motif' => null]]] as [$incorporation, $sortie, $motif]) {
            $data = [
                'incorporation' => $incorporation,
                'sortie' => $sortie,
                'localite_id' => $this->localiteId,
                ...$motif,
            ];

            $response = $this->json('POST', "/api/v2/sapeurs/{$this->sapeurId}/mutations", $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.mutation.motif', '');
        }
    }

    public function testAddMutationEnglobanteRefusee()
    {
        // Mutation existante 2005-2008
        $this->json('POST', "/api/v2/sapeurs/{$this->sapeurId}/mutations", [
            'incorporation' => "2005-01-01",
            'sortie' => "2008-12-31",
            'motif' => 'Première période',
            'localite_id' => $this->localiteId
        ])->assertStatus(201)->assertJsonMissingPath('error');

        // Mutation englobant complètement la première → conflit
        $response = $this->json('POST', "/api/v2/sapeurs/{$this->sapeurId}/mutations", [
            'incorporation' => "2004-01-01",
            'sortie' => "2009-12-31",
            'motif' => 'Période englobante',
            'localite_id' => $this->localiteId
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.incorporation', 'Deux mutations en conflits');
    }

    public function testEditMutationSuccessfully()
    {
        // First, create a mutation with dates BEFORE the initial one (2019)
        $createData = [
            'incorporation' => "2010-01-01",
            'sortie' => "2014-12-31",
            'motif' => 'Original mutation',
            'localite_id' => $this->localiteId
        ];

        $createResponse = $this->json('POST', "/api/v2/sapeurs/{$this->sapeurId}/mutations", $createData);
        $mutationId = $createResponse->json('data.mutation.id');

        // Now update it (keeping dates non-overlapping with other mutations)
        $updateData = [
            'incorporation' => "2007-01-01",
            'sortie' => "2009-12-31",
            'motif' => 'Updated mutation',
            'localite_id' => $this->localiteId,
            'id' => $mutationId
        ];

        $response = $this->json('PUT', "/api/v2/sapeurs/{$this->sapeurId}/mutations/{$mutationId}", $updateData);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'mutation' => ['id', 'localite_id', 'incorporation', 'sapeur_id', 'sortie', 'motif'],
                    'statut_sapeur'
                ]
            ])
            ->assertJsonPath('data.mutation.id', $mutationId)
            ->assertJsonPath('data.mutation.motif', $updateData['motif'])
            ->assertJsonPath('data.mutation.localite_id', $updateData['localite_id']);

        // Verify dates
        $mutationIncorporation = Carbon::parse($response->json('data.mutation.incorporation'));
        $expectedIncorporation = Carbon::parse($updateData['incorporation']);
        $this->assertTrue($mutationIncorporation->isSameDay($expectedIncorporation));

        $mutationSortie = Carbon::parse($response->json('data.mutation.sortie'));
        $expectedSortie = Carbon::parse($updateData['sortie']);
        $this->assertTrue($mutationSortie->isSameDay($expectedSortie));
    }

    public function testEditMutationReturnsErrorWhenNotFound()
    {
        $updateData = [
            'incorporation' => "2022-01-16",
            'sortie' => null,
            'motif' => 'Updated mutation',
            'localite_id' => $this->localiteId,
            'id' => 999999
        ];

        $response = $this->json('PUT', "/api/v2/sapeurs/{$this->sapeurId}/mutations/999999", $updateData);

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function testRemoveMutationSuccessfully()
    {
        // First, create a mutation to delete with non-overlapping dates (before 2019)
        // Use dates well before the factory mutation (2019-01-29)
        $createData = [
            'incorporation' => "2012-01-01",
            'sortie' => "2014-12-31",
            'motif' => 'To be deleted',
            'localite_id' => $this->localiteId
        ];

        $createResponse = $this->json('POST', "/api/v2/sapeurs/{$this->sapeurId}/mutations", $createData);
        $mutationId = $createResponse->json('data.mutation.id');

        // Delete it
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$this->sapeurId}/mutations/{$mutationId}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.type', SapeurBusiness::TYPE_SAPEUR);

        // Verify it's deleted via GET
        $getResponse = $this->json('GET', "/api/v2/sapeurs/{$this->sapeurId}/mutations");
        $mutations = $getResponse->json('data');
        $this->assertNotContains($mutationId, array_column($mutations, 'id'));
    }

    public function testRemoveMutationReturnsErrorWhenNotFound()
    {
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$this->sapeurId}/mutations/999999");

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    private function onlyMutationId(): int
    {
        return Mutation::where('sapeur_id', $this->sapeurId)->sole()->id;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createExerciceSapeur(array $attributes = []): void
    {
        // La factory laisse les colonnes texte à null alors qu'elles sont NOT NULL en base.
        ExerciceSapeur::factory()->create([
            'sapeur_id' => $this->sapeurId,
            'excuse_statut' => 0,
            'date_excuse' => now(),
            'remarque' => '',
            'justificatif_path' => '',
            'justificatif_filename' => '',
            'justification' => '',
            ...$attributes,
        ]);
    }

    private function assertLastMutationDeletionRefused(): void
    {
        $mutationId = $this->onlyMutationId();

        $response = $this->json('DELETE', "/api/v2/sapeurs/{$this->sapeurId}/mutations/{$mutationId}");

        $response
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
        $this->assertDatabaseHas('mutations', ['id' => $mutationId]);
        $this->assertDatabaseHas('sapeurs', ['id' => $this->sapeurId, 'statut_sapeur' => SapeurBusiness::STATUT_ACTIF]);
    }

    public function testRemoveLastMutationTurnsSapeurIntoRecrue()
    {
        $mutationId = $this->onlyMutationId();

        $response = $this->json('DELETE', "/api/v2/sapeurs/{$this->sapeurId}/mutations/{$mutationId}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.type', SapeurBusiness::TYPE_RECRUE);
        $this->assertDatabaseHas('sapeurs', [
            'id' => $this->sapeurId,
            'statut_sapeur' => SapeurBusiness::STATUT_JAMAIS,
            'statut_civil' => SapeurBusiness::STATUT_JAMAIS,
            'annee_incorporation' => '',
        ]);
        $this->assertDatabaseMissing('mutations', ['sapeur_id' => $this->sapeurId]);
    }

    public function testRemoveLastMutationRefusedWhenSapeurHasExercice()
    {
        $this->createExerciceSapeur(['convoque' => false]);

        $this->assertLastMutationDeletionRefused();
    }

    public function testRemoveLastMutationRefusedWhenSapeurHasIntervention()
    {
        $interventionSapeur = new InterventionSapeur();
        $interventionSapeur->intervention_id = Intervention::factory()->create()->id;
        $interventionSapeur->sapeur_id = $this->sapeurId;
        $interventionSapeur->debut = '2024-01-01 12:00';
        $interventionSapeur->fin = '2024-01-01 13:00';
        $interventionSapeur->piquet = false;
        $interventionSapeur->save();

        $this->assertLastMutationDeletionRefused();
    }

    public function testRemoveLastMutationRefusedWhenSapeurIsChefIntervention()
    {
        Intervention::factory()->create(['sapeur_id' => $this->sapeurId]);

        $this->assertLastMutationDeletionRefused();
    }

    public function testRemoveLastMutationRefusedWhenSapeurHasEcriture()
    {
        $intervention = Intervention::factory()->create();
        Ecriture::create([
            'designation' => 'test',
            'total' => 10,
            'tarif' => 10,
            'quantite' => 1,
            'type_unite_id' => 1,
            'type' => 1,
            'sapeur_id' => $this->sapeurId,
            'compte_id' => 1,
            'ecriture_categorie_id' => 1,
            'exercice_comptable_id' => $intervention->exercice_comptable_id,
        ]);

        $this->assertLastMutationDeletionRefused();
    }

    public function testRemoveLastMutationRefusedWhenSapeurHasTravail()
    {
        $typeId = TravauxParamBusiness::ajouterType([
            'designation' => 'Rédition',
            'actif' => true,
            'ecriture_categorie_id' => 1,
            'type_unite_id' => 2,
            'lien_exercice' => true,
            'lien_intervention' => true,
            'fonctions' => [['type' => 1, 'tarif' => 30.00, 'compte_id' => 1, 'fonction_id' => null]],
        ])->id;
        Travail::create([
            'designation' => 'Rédition',
            'date' => '2026-10-13',
            'date_demande' => '2026-10-13',
            'quantite' => 2,
            'justification' => 'à vider',
            'statut' => 0,
            'sapeur_id' => $this->sapeurId,
            'auteur_id' => $this->sapeurId,
            'travail_type_id' => $typeId,
            'exercice_comptable_id' => Intervention::factory()->create()->exercice_comptable_id,
        ]);

        $this->assertLastMutationDeletionRefused();
    }

    public function testRemoveNonLastMutationAllowedWhenSapeurHasExercice()
    {
        $this->createExerciceSapeur();
        $mutationId = $this->json('POST', "/api/v2/sapeurs/{$this->sapeurId}/mutations", [
            'incorporation' => '2012-01-01',
            'sortie' => '2014-12-31',
            'localite_id' => $this->localiteId,
        ])->json('data.mutation.id');

        $response = $this->json('DELETE', "/api/v2/sapeurs/{$this->sapeurId}/mutations/{$mutationId}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.type', SapeurBusiness::TYPE_SAPEUR);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function postMutationCivile(int $sapeurId, array $attributes = []): \Illuminate\Testing\TestResponse
    {
        return $this->json('POST', "/api/v2/sapeurs/{$sapeurId}/mutations", [
            'nature' => SapeurBusiness::NATURE_CIVIL,
            'incorporation' => now()->subMonth()->toDateString(),
            'localite_id' => $this->localiteId,
            ...$attributes,
        ]);
    }

    /**
     * Un sapeur en service peut être civil en même temps : pas de conflit entre natures
     */
    public function testAddMutationCivileAcceptedWhileSapeurMutationIsOpen()
    {
        $response = $this->postMutationCivile($this->sapeurId);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.mutation.nature', SapeurBusiness::NATURE_CIVIL)
            ->assertJsonPath('data.statut_sapeur', SapeurBusiness::STATUT_ACTIF)
            ->assertJsonPath('data.statut_civil', SapeurBusiness::STATUT_ACTIF)
            ->assertJsonPath('data.type', SapeurBusiness::TYPE_SAPEUR);
        $this->assertDatabaseHas('sapeurs', [
            'id' => $this->sapeurId,
            'statut_sapeur' => SapeurBusiness::STATUT_ACTIF,
            'statut_civil' => SapeurBusiness::STATUT_ACTIF,
        ]);
    }

    public function testAddMutationCivileOverlappingAnotherCivileReturns422()
    {
        $this->postMutationCivile($this->sapeurId, ['incorporation' => '2020-01-01', 'sortie' => '2022-12-31'])->assertStatus(201);

        $response = $this->postMutationCivile($this->sapeurId, ['incorporation' => '2021-01-01', 'sortie' => '2021-12-31']);

        $response->assertStatus(422);
        $this->assertSame(1, Mutation::where('sapeur_id', $this->sapeurId)->where('nature', SapeurBusiness::NATURE_CIVIL)->count());
    }

    public function testAncienSapeurDevenuCivilApparaitCommeCivilActif()
    {
        $mutationId = $this->onlyMutationId();
        $this->json('PUT', "/api/v2/sapeurs/{$this->sapeurId}/mutations/{$mutationId}", [
            'id' => $mutationId,
            'incorporation' => '2010-01-01',
            'sortie' => now()->subMonth()->toDateString(),
        ])->assertStatus(200);

        $response = $this->postMutationCivile($this->sapeurId, ['incorporation' => now()->subMonth()->addDay()->toDateString()]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.statut_sapeur', SapeurBusiness::STATUT_INACTIF)
            ->assertJsonPath('data.statut_civil', SapeurBusiness::STATUT_ACTIF)
            ->assertJsonPath('data.type', SapeurBusiness::TYPE_CIVIL)
            // L'année d'incorporation reste celle du service, pas de la période civile
            ->assertJsonPath('data.annee_incorporation', 2010);
    }

    /**
     * Un civil qui devient sapeur garde sa mutation civile ouverte
     */
    public function testAddMutationSapeurToCivilKeepsCivilMutationOpen()
    {
        $civil = Sapeur::factory()->civil()->create(['localite_id' => $this->localiteId]);

        $response = $this->json('POST', "/api/v2/sapeurs/{$civil->id}/mutations", [
            'incorporation' => now()->subWeek()->toDateString(),
            'localite_id' => $this->localiteId,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.statut_sapeur', SapeurBusiness::STATUT_ACTIF)
            ->assertJsonPath('data.statut_civil', SapeurBusiness::STATUT_ACTIF);
        $this->assertDatabaseHas('mutations', ['sapeur_id' => $civil->id, 'nature' => SapeurBusiness::NATURE_CIVIL, 'sortie' => null]);
    }

    public function testAddMutationToRecrueReturns422()
    {
        $recrue = Sapeur::factory()->recrue()->create(['localite_id' => $this->localiteId]);

        $response = $this->postMutationCivile($recrue->id);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('mutations', ['sapeur_id' => $recrue->id]);
    }

    public function testRemoveLastMutationOfCivilReturns422()
    {
        $civil = Sapeur::factory()->civil()->create(['localite_id' => $this->localiteId]);
        $mutationId = Mutation::where('sapeur_id', $civil->id)->sole()->id;

        $response = $this->json('DELETE', "/api/v2/sapeurs/{$civil->id}/mutations/{$mutationId}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('mutations', ['id' => $mutationId]);
    }

    /**
     * Supprimer la mutation sapeur d'une personne aussi civile la laisse civile, pas recrue
     */
    public function testRemoveSapeurMutationKeepsCivilStatus()
    {
        $this->postMutationCivile($this->sapeurId)->assertStatus(201);
        $mutationId = Mutation::where('sapeur_id', $this->sapeurId)->where('nature', SapeurBusiness::NATURE_SAPEUR)->sole()->id;

        $response = $this->json('DELETE', "/api/v2/sapeurs/{$this->sapeurId}/mutations/{$mutationId}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.statut_sapeur', SapeurBusiness::STATUT_JAMAIS)
            ->assertJsonPath('data.statut_civil', SapeurBusiness::STATUT_ACTIF)
            ->assertJsonPath('data.type', SapeurBusiness::TYPE_CIVIL);
    }
}
