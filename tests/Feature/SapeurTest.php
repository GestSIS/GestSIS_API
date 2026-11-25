<?php

namespace Tests\Feature;

use App\Models\ArticleAttribution;
use App\Models\Localite;
use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use App\Models\SapeurTelephone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurTest extends TestCase
{
    use DatabaseTransactions;

    protected $localiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $localite = Localite::firstOrCreate(
            ['id' => 1],
            [
                'commune_id' => null,
                'npa' => '2800',
                'designation' => 'Test Localité',
            ]
        );

        $this->localiteId = $localite->id;
    }

    /**
     * Les champs booléens du sapeur sont exposés en true/false dans l'API
     */
    public function testIndexExposeLesBooleensSapeur()
    {
        Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs');

        $response->assertStatus(200);
        $this->assertIsBool($response->json('data.0.actif'));
    }

    /**
     * Le type principal reste exposé (affichage, anciennes versions de l'app mobile qui
     * filtrent sur `type`) : un ancien sapeur resté civil apparaît comme civil.
     */
    public function testIndexExposeLesStatutsEtLeTypePrincipal()
    {
        $sapeur = Sapeur::factory()->ancienSapeurCivil()->create(['localite_id' => $this->localiteId]);

        $response = $this->json('GET', '/api/v2/sapeurs');

        $response->assertStatus(200);
        $data = collect($response->json('data'))->firstWhere('id', $sapeur->id);
        $this->assertSame(SapeurBusiness::STATUT_INACTIF, $data['statut_sapeur']);
        $this->assertSame(SapeurBusiness::STATUT_ACTIF, $data['statut_civil']);
        $this->assertSame(SapeurBusiness::TYPE_CIVIL, $data['type']);
    }

    public function testIndexSapeurReturnsListOfSapeurs()
    {
        $sapeur1 = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $sapeur2 = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nom', 'prenom', 'fonction_id', 'actif']
                ]
            ]);

        // Check that our created sapeurs are in the response
        $responseData = $response->json('data');
        $this->assertContains($sapeur1->id, array_column($responseData, 'id'));
        $this->assertContains($sapeur2->id, array_column($responseData, 'id'));
    }

    public function testShowSapeurReturnsSpecificSapeur()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs/' . $sapeur->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nom',
                    'prenom',
                    'fonction_id',
                    'statut_sapeur',
                    'date_naissance'
                ]
            ])
            ->assertJsonPath('data.id', $sapeur->id)
            ->assertJsonPath('data.nom', $sapeur->nom)
            ->assertJsonPath('data.prenom', $sapeur->prenom);
    }

    /**
     * La fiche inclut les numéros de téléphone du sapeur (et seulement les siens)
     */
    public function testShowSapeurIncludesTelephones()
    {
        $sapeur = Sapeur::factory()->create(['localite_id' => $this->localiteId]);
        $autreSapeur = Sapeur::factory()->create(['localite_id' => $this->localiteId]);
        $telephone = SapeurTelephone::factory()->create([
            'sapeur_id' => $sapeur->id,
            'telephone_type_id' => 1,
        ]);
        SapeurTelephone::factory()->create([
            'sapeur_id' => $autreSapeur->id,
            'telephone_type_id' => 1,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs/' . $sapeur->id);

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.telephones')
            ->assertJsonPath('data.telephones.0.id', $telephone->id)
            ->assertJsonPath('data.telephones.0.numero', $telephone->numero);
    }

    public function testShowSapeurReturnsErrorWhenNotFound()
    {
        $response = $this->json('GET', '/api/v2/sapeurs/999999');

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function testCreateSapeurSuccessfully()
    {
        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29"; // ISO format
        $data['localite_id'] = $this->localiteId; // Use existing localite
        $data['nature'] = SapeurBusiness::NATURE_SAPEUR;

        $response = $this->json('POST', '/api/v2/sapeurs', $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nom',
                    'prenom',
                    'date_naissance'
                ]
            ])
            ->assertJsonPath('data.nom', $data['nom'])
            ->assertJsonPath('data.prenom', $data['prenom'])
            ->assertJsonPath('data.fonction_id', 0)
            ->assertJsonPath('data.statut_sapeur', SapeurBusiness::STATUT_ACTIF)
            ->assertJsonPath('data.statut_civil', SapeurBusiness::STATUT_JAMAIS)
            ->assertJsonPath('data.type', SapeurBusiness::TYPE_SAPEUR);

        $sapeurId = $response->json('data.id');
        $this->assertNotNull($sapeurId);

        // Verify mutation was created via REST API
        $mutationsResponse = $this->json('GET', '/api/v2/sapeurs/' . $sapeurId . '/mutations');
        $mutationsResponse->assertStatus(200);
        $this->assertCount(1, $mutationsResponse->json('data'));
    }

    public function testUpdateSapeurSuccessfully()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        // Prepare update data
        $updateData = Sapeur::factory()->make()->toArray();
        $updateData['localite_id'] = $this->localiteId;

        $response = $this->json('PUT', '/api/v2/sapeurs/' . $sapeur->id, $updateData);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nom',
                    'prenom',
                    'date_naissance'
                ]
            ])
            ->assertJsonPath('data.id', $sapeur->id)
            ->assertJsonPath('data.nom', $updateData['nom'])
            ->assertJsonPath('data.prenom', $updateData['prenom']);

        // Verify the sapeur was actually updated via REST API
        $getResponse = $this->json('GET', '/api/v2/sapeurs/' . $sapeur->id);
        $getResponse->assertStatus(200);
        $this->assertEquals($updateData['nom'], $getResponse->json('data.nom'));
        $this->assertEquals($updateData['prenom'], $getResponse->json('data.prenom'));
    }

    public function testPartialUpdateOfProReferencesKeepsAvsAndEmail()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'no_avs' => '756.1234.5678.97',
            'email' => 'sapeur@example.com',
            'lieu_de_travail' => 'Ancien lieu',
        ]);

        // Frontend "Références professionnelles" save sends only the pro fields.
        $this->json('PUT', '/api/v2/sapeurs/' . $sapeur->id, [
            'profession' => 'Mécanicien',
            'employeur' => 'Garage SA',
            'lieu_de_travail' => 'Nouveau lieu',
        ])->assertStatus(200);

        $sapeur->refresh();
        $this->assertEquals('Nouveau lieu', $sapeur->lieu_de_travail);
        $this->assertEquals('756.1234.5678.97', $sapeur->no_avs);
        $this->assertEquals('sapeur@example.com', $sapeur->email);
    }

    public function testPartialUpdateOfPersonalDataKeepsProReferences()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'profession' => 'Mécanicien',
            'employeur' => 'Garage SA',
            'lieu_de_travail' => 'Atelier',
        ]);

        // Frontend "Données personnelles" save sends only the personal fields.
        $this->json('PUT', '/api/v2/sapeurs/' . $sapeur->id, [
            'nom' => 'Nouveau',
            'prenom' => 'Prenom',
            'email' => 'new@example.com',
        ])->assertStatus(200);

        $sapeur->refresh();
        $this->assertEquals('Mécanicien', $sapeur->profession);
        $this->assertEquals('Garage SA', $sapeur->employeur);
        $this->assertEquals('Atelier', $sapeur->lieu_de_travail);
    }

    public function testUpdateSapeurRefuseUnAvsAuFormatInvalide()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'no_avs' => '756.1234.5678.97',
        ]);

        $response = $this->json('PUT', '/api/v2/sapeurs/' . $sapeur->id, [
            'no_avs' => '756.1234.5678.98', // clé de contrôle erronée
        ]);

        $response->assertStatus(422)->assertJsonStructure(['message']);
        $this->assertEquals('756.1234.5678.97', $sapeur->fresh()->no_avs);
    }

    public function testUpdateSapeurRefuseUnIbanInvalide()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'iban' => 'CH93 0076 2011 6238 5295 7',
        ]);

        $response = $this->json('PUT', '/api/v2/sapeurs/' . $sapeur->id, [
            'iban' => 'CH93 0076 2011 6238 5295 8', // clé de contrôle erronée
        ]);

        $response->assertStatus(422)->assertJsonPath('errors', ['iban' => 'IBAN invalide.']);
        $this->assertEquals('CH93 0076 2011 6238 5295 7', $sapeur->fresh()->iban);
    }

    public function testUpdateSapeurAccepteUnIbanValide()
    {
        $sapeur = Sapeur::factory()->create(['localite_id' => $this->localiteId]);

        $response = $this->json('PUT', '/api/v2/sapeurs/' . $sapeur->id, [
            'iban' => 'CH93 0076 2011 6238 5295 7',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('CH93 0076 2011 6238 5295 7', $sapeur->fresh()->iban);
    }

    public function testUpdateSapeurAccepteUnIbanVide()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'iban' => 'CH93 0076 2011 6238 5295 7',
        ]);

        $response = $this->json('PUT', '/api/v2/sapeurs/' . $sapeur->id, ['iban' => null]);

        $response->assertStatus(200);
        $this->assertEquals('', $sapeur->fresh()->iban);
    }

    public function testUpdateSapeurReturnsErrorWhenNotFound()
    {
        $data = Sapeur::factory()->make()->toArray();

        $response = $this->json('PUT', '/api/v2/sapeurs/99999999', $data);

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function testDestroySapeurSuccessfully()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('DELETE', '/api/v2/sapeurs/' . $sapeur->id);

        $response->assertStatus(204);

        // Verify the sapeur was actually deleted
        $getResponse = $this->json('GET', '/api/v2/sapeurs/' . $sapeur->id);
        $getResponse->assertStatus(404);
    }

    public function testSupprimerSapeurAyantEuDuMaterielEstRefuse()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);
        // Attribution close : le matériel a été rendu, mais l'historique doit être conservé
        ArticleAttribution::factory()->create([
            'sapeur_id' => $sapeur->id,
            'debut' => '2026-01-01',
            'fin' => '2026-02-01',
        ]);

        $this->json('DELETE', '/api/v2/sapeurs/' . $sapeur->id)
            ->assertStatus(422)
            ->assertJson(['message' => 'Impossible de supprimer un sapeur ayant eu du matériel attribué, désactivez-le plutôt']);

        $this->assertDatabaseHas('sapeurs', ['id' => $sapeur->id]);
    }

    public function testDestroySapeurReturnsErrorWhenNotFound()
    {
        $response = $this->json('DELETE', '/api/v2/sapeurs/99999999');

        $response
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function testEffectifReturnsActiveFirefighters()
    {
        $sapeurActif = Sapeur::factory()->create(['localite_id' => $this->localiteId]);
        $sapeurInactif = Sapeur::factory()->inactif()->create(['localite_id' => $this->localiteId]);
        $civil = Sapeur::factory()->civil()->create(['localite_id' => $this->localiteId]);
        // Actif (en tant que civil) mais plus en service : hors effectif
        $ancienSapeurCivil = Sapeur::factory()->ancienSapeurCivil()->create(['localite_id' => $this->localiteId]);

        $response = $this->json('GET', '/api/v2/effectif');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nom', 'prenom', 'email']
                ]
            ]);

        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($sapeurActif->id, $ids);
        $this->assertNotContains($sapeurInactif->id, $ids);
        $this->assertNotContains($civil->id, $ids);
        $this->assertNotContains($ancienSapeurCivil->id, $ids);
    }

    public function testCreateCivilCreeUneMutationCivile()
    {
        $response = $this->json('POST', '/api/v2/sapeurs', [
            'nature' => SapeurBusiness::NATURE_CIVIL,
            'nom' => 'Dupont',
            'prenom' => 'Marie',
            'rue' => 'Rue du Test',
            'no_rue' => '1',
            'incorporation' => '2024-03-01',
            'localite_id' => $this->localiteId,
            'civilite_id' => 1,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.statut_sapeur', SapeurBusiness::STATUT_JAMAIS)
            ->assertJsonPath('data.statut_civil', SapeurBusiness::STATUT_ACTIF)
            ->assertJsonPath('data.type', SapeurBusiness::TYPE_CIVIL)
            ->assertJsonPath('data.annee_incorporation', '');
        $this->assertDatabaseHas('mutations', [
            'sapeur_id' => $response->json('data.id'),
            'nature' => SapeurBusiness::NATURE_CIVIL,
            'incorporation' => '2024-03-01',
            'sortie' => null,
            'localite_id' => $this->localiteId,
        ]);
    }

    public function testCreateCivilRefuseSansDateIncorporation()
    {
        $response = $this->json('POST', '/api/v2/sapeurs', [
            'nature' => SapeurBusiness::NATURE_CIVIL,
            'nom' => 'Dupont',
            'prenom' => 'Marie',
            'rue' => 'Rue du Test',
            'no_rue' => '1',
            'localite_id' => $this->localiteId,
            'civilite_id' => 1,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['incorporation']);
    }

    public function testTrombinoscopeReturnsResponse()
    {
        // Create some sapeurs for the trombinoscope
        Sapeur::factory()->count(3)->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/trombinoscope', [], ['Sis-Key' => 1]);

        // Should return a response (PDF or error structure)
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function testFicheReturnsResponseForSapeur()
    {
        $sapeur = Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs/' . $sapeur->id . '/fiche', [], ['Sis-Key' => 1]);

        // Should return a response (PDF or error structure)
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function testListeFsspReturnsResponse()
    {
        Sapeur::factory()->count(2)->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/liste-fssp');

        $response->assertStatus(200);
    }

    public function testListeFoadReturnsResponse()
    {
        Sapeur::factory()->count(2)->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/liste-foad');

        $response->assertStatus(200);
    }

    public function testSapeursTelephonesReturnsData()
    {
        Sapeur::factory()->count(2)->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs-telephones');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function testConvocationSmsReturnsData()
    {
        Sapeur::factory()->count(2)->create([
            'localite_id' => $this->localiteId,
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs-convocation');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function testConvocationSmsContientLesSapeursEtCivilsActifs(): void
    {
        $sapeur = Sapeur::factory()->create(['localite_id' => $this->localiteId]);
        $civil = Sapeur::factory()->civil()->create(['localite_id' => $this->localiteId]);
        $ancienSapeur = Sapeur::factory()->inactif()->create(['localite_id' => $this->localiteId]);
        $recrue = Sapeur::factory()->recrue()->create(['localite_id' => $this->localiteId]);

        $ids = collect($this->json('GET', '/api/v2/sapeurs-convocation')->assertStatus(200)->json('data'))->pluck('id');

        $this->assertContains($sapeur->id, $ids);
        $this->assertContains($civil->id, $ids);
        $this->assertNotContains($ancienSapeur->id, $ids);
        $this->assertNotContains($recrue->id, $ids);
    }

    /**
     * Champ actif calculé, gardé pour les anciennes versions des apps mobiles
     */
    public function testIndexCalculeActifDepuisLesStatuts(): void
    {
        $sapeur = Sapeur::factory()->create(['localite_id' => $this->localiteId]);
        $civil = Sapeur::factory()->civil()->create(['localite_id' => $this->localiteId]);
        $ancienSapeur = Sapeur::factory()->inactif()->create(['localite_id' => $this->localiteId]);
        $recrue = Sapeur::factory()->recrue()->create(['localite_id' => $this->localiteId]);

        $actifs = collect($this->json('GET', '/api/v2/sapeurs')->assertStatus(200)->json('data'))->pluck('actif', 'id');

        $this->assertTrue($actifs[$sapeur->id]);
        $this->assertTrue($actifs[$civil->id]);
        $this->assertFalse($actifs[$ancienSapeur->id]);
        $this->assertFalse($actifs[$recrue->id]);
    }

    public function testIndexFiltreActifGardeLesSapeursEtCivilsActifs(): void
    {
        $sapeur = Sapeur::factory()->create(['localite_id' => $this->localiteId]);
        $civil = Sapeur::factory()->civil()->create(['localite_id' => $this->localiteId]);
        $ancienSapeur = Sapeur::factory()->inactif()->create(['localite_id' => $this->localiteId]);

        $ids = collect($this->json('GET', '/api/v2/sapeurs?actif=true')->assertStatus(200)->json('data'))->pluck('id');

        $this->assertContains($sapeur->id, $ids);
        $this->assertContains($civil->id, $ids);
        $this->assertNotContains($ancienSapeur->id, $ids);
    }
}
