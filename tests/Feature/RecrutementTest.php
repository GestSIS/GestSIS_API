<?php

namespace Tests\Feature;

use App\Domaine\Business\RecrutementTokenBusiness;
use App\Domaine\Business\SapeurBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Civilite;
use App\Models\Localite;
use App\Models\Permis;
use App\Models\PermisType;
use App\Models\RecrutementToken;
use App\Models\Sapeur;
use App\Models\SapeurTelephone;
use App\Models\TelephoneType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RecrutementTest extends TestCase
{
    use DatabaseTransactions;

    protected $localiteId;
    protected $civiliteId;
    protected $telephoneTypeId;
    protected $permisTypeId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localiteId = Localite::firstOrCreate(
            ['id' => 1],
            ['commune_id' => null, 'npa' => '2800', 'designation' => 'Test Localité']
        )->id;

        $this->civiliteId = Civilite::firstOrCreate(
            ['id' => 1],
            ['designation' => 'Monsieur', 'forme_politesse' => 'Monsieur']
        )->id;

        $this->telephoneTypeId = TelephoneType::firstOrCreate(
            ['id' => 1],
            ['type' => 'Portable']
        )->id;

        $this->permisTypeId = PermisType::firstOrCreate(
            ['id' => 1],
            ['type' => 'B']
        )->id;
    }

    private function formulaireRecrue(array $overrides = []): array
    {
        return array_merge([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'rue' => 'Rue de la Gare',
            'no_rue' => '12',
            'date_naissance' => '2000-01-15',
            'localite_id' => $this->localiteId,
            'civilite_id' => $this->civiliteId,
            'no_avs' => '756.1234.5678.97',
            'cotisation_avs' => true,
            'profession' => 'Mécanicien',
            'employeur' => 'Garage SA',
            'lieu_de_travail' => 'Delémont',
            'email' => 'jean.dupont@example.com',
            'iban' => 'CH93 0076 2011 6238 5295 7',
            'telephones' => [
                ['numero' => '079 123 45 67', 'telephone_type_id' => $this->telephoneTypeId, 'priorite' => 1],
            ],
        ], $overrides);
    }

    // --- Gestion du jeton (fourrier) ---

    public function testGenererTokenRetourneLeJetonEnClairUneFois()
    {
        $response = $this->json('POST', '/api/v2/recrutement/token', ['duree_heures' => 12]);

        $response->assertStatus(200)->assertJsonStructure(['data' => ['token', 'expire_at']]);
        $this->assertNotEmpty($response->json('data.token'));
        $this->assertCount(1, RecrutementToken::all());
    }

    public function testGenererTokenRefuseDureeSuperieureA24Heures()
    {
        $response = $this->json('POST', '/api/v2/recrutement/token', ['duree_heures' => 25]);

        // Convention du projet : les erreurs de validation Laravel sont réécrites en 200 + clé "error"
        $response->assertStatus(200)->assertJsonStructure(['error']);
        $this->assertCount(0, RecrutementToken::all());
    }

    public function testResetInvalideLAncienJeton()
    {
        $premiere = $this->json('POST', '/api/v2/recrutement/token', ['duree_heures' => 12]);
        $premierToken = $premiere->json('data.token');

        $this->json('POST', '/api/v2/recrutement/token', ['duree_heures' => 6]);

        $this->assertFalse(RecrutementTokenBusiness::verifierToken($premierToken));
        $this->assertCount(1, RecrutementToken::all());
    }

    public function testShowTokenNeRevelePasLeJetonEnClair()
    {
        $this->json('POST', '/api/v2/recrutement/token', ['duree_heures' => 12]);

        $response = $this->json('GET', '/api/v2/recrutement/token');

        $response->assertStatus(200)->assertJsonStructure(['data' => ['expire_at']]);
        $this->assertArrayNotHasKey('token', $response->json('data'));
    }

    public function testDestroyInvalideLeJetonActif()
    {
        $this->json('POST', '/api/v2/recrutement/token', ['duree_heures' => 12]);
        $this->json('DELETE', '/api/v2/recrutement/token')->assertStatus(200);

        $this->assertCount(0, RecrutementToken::all());
    }

    // --- Formulaire public ---

    public function testShowPublicIndiqueUnJetonValide()
    {
        [$tokenEnClair] = RecrutementTokenBusiness::genererToken(12);

        $response = $this->json('GET', "/api/v2/recrutement/test/{$tokenEnClair}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.valide', true)
            ->assertJsonStructure(['data' => ['civilites', 'localites', 'telephoneTypes', 'permisTypes']]);
        $this->assertContains($this->civiliteId, array_column($response->json('data.civilites'), 'id'));
    }

    public function testShowPublicIndiqueUnJetonExpire()
    {
        [$tokenEnClair, $token] = RecrutementTokenBusiness::genererToken(12);
        $token->update(['expire_at' => now()->subHour()]);

        $response = $this->json('GET', "/api/v2/recrutement/test/{$tokenEnClair}");

        $response->assertStatus(200)->assertJsonPath('data.valide', false);
    }

    public function testStorePublicCreeUneRecrueAvecSesTelephones()
    {
        [$tokenEnClair] = RecrutementTokenBusiness::genererToken(12);

        $response = $this->json('POST', "/api/v2/recrutement/test/{$tokenEnClair}", $this->formulaireRecrue());

        $response->assertStatus(200)->assertJsonStructure(['data' => ['id']]);

        $recrue = Sapeur::find($response->json('data.id'));
        $this->assertNotNull($recrue);
        $this->assertEquals(SapeurBusiness::TYPE_RECRUE, $recrue->type);
        $this->assertFalse((bool) $recrue->actif);
        $this->assertCount(1, SapeurTelephone::where('sapeur_id', $recrue->id)->get());
    }

    public function testStorePublicCreeUneRecrueAvecSesPermis()
    {
        [$tokenEnClair] = RecrutementTokenBusiness::genererToken(12);

        $response = $this->json(
            'POST',
            "/api/v2/recrutement/test/{$tokenEnClair}",
            $this->formulaireRecrue([
                'permis' => [
                    ['permis_type_id' => $this->permisTypeId, 'date' => '2018-06-01'],
                ],
            ]),
        );

        $response->assertStatus(200)->assertJsonStructure(['data' => ['id']]);

        $recrueId = $response->json('data.id');
        $permis = Permis::where('sapeur_id', $recrueId)->get();
        $this->assertCount(1, $permis);
        $this->assertEquals($this->permisTypeId, $permis->first()->permis_type_id);
    }

    public function testStorePublicBloqueUnPermisEnDouble()
    {
        [$tokenEnClair] = RecrutementTokenBusiness::genererToken(12);

        $response = $this->json(
            'POST',
            "/api/v2/recrutement/test/{$tokenEnClair}",
            $this->formulaireRecrue([
                'permis' => [
                    ['permis_type_id' => $this->permisTypeId, 'date' => '2018-06-01'],
                    ['permis_type_id' => $this->permisTypeId, 'date' => '2020-01-01'],
                ],
            ]),
        );

        // Convention du projet : les erreurs de validation Laravel sont réécrites en 200 + clé "error"
        $response->assertStatus(200)->assertJsonStructure(['error']);
        $this->assertCount(0, Sapeur::where('no_avs', '756.1234.5678.97')->get());
    }

    public function testCreateRecrueNeLaissePasDeRecrueOrphelineSiUnPermisEstEnDouble()
    {
        // Contourne la validation HTTP (distinct) pour vérifier que la transaction protège aussi
        // au niveau métier : si SapeurBusiness::addPermis() refuse le second permis, la recrue
        // elle-même (et son premier permis) ne doivent pas rester en base.
        $this->expectException(ArrayException::class);

        try {
            SapeurBusiness::createRecrue($this->formulaireRecrue([
                'permis' => [
                    ['permis_type_id' => $this->permisTypeId, 'date' => '2018-06-01'],
                    ['permis_type_id' => $this->permisTypeId, 'date' => '2020-01-01'],
                ],
            ]));
        } finally {
            $this->assertCount(0, Sapeur::where('no_avs', '756.1234.5678.97')->get());
        }
    }

    public function testStorePublicFonctionneSansPermis()
    {
        [$tokenEnClair] = RecrutementTokenBusiness::genererToken(12);

        $response = $this->json('POST', "/api/v2/recrutement/test/{$tokenEnClair}", $this->formulaireRecrue());

        $response->assertStatus(200);
        $this->assertCount(0, Permis::where('sapeur_id', $response->json('data.id'))->get());
    }

    public function testStorePublicRefuseUnJetonInvalide()
    {
        $response = $this->json('POST', '/api/v2/recrutement/test/jeton-invalide', $this->formulaireRecrue());

        $response->assertStatus(404);
    }

    public function testStorePublicBloqueUnAvsDejaExistant()
    {
        Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'no_avs' => '756.1234.5678.97',
        ]);
        [$tokenEnClair] = RecrutementTokenBusiness::genererToken(12);

        $response = $this->json('POST', "/api/v2/recrutement/test/{$tokenEnClair}", $this->formulaireRecrue());

        $response->assertStatus(200)->assertJsonStructure(['error']);
        $this->assertCount(1, Sapeur::where('no_avs', '756.1234.5678.97')->get());
    }

    public function testStorePublicBloqueUnAvsDejaExistantMemeAvecFormatDifferent()
    {
        Sapeur::factory()->create([
            'localite_id' => $this->localiteId,
            'no_avs' => '7561234567897', // même AVS, sans ponctuation
        ]);
        [$tokenEnClair] = RecrutementTokenBusiness::genererToken(12);

        $response = $this->json('POST', "/api/v2/recrutement/test/{$tokenEnClair}", $this->formulaireRecrue());

        $response->assertStatus(200)->assertJsonStructure(['error']);
    }

    // --- Validation / rejet (fourrier) ---

    public function testValiderBasculeLaRecrueEnSapeurEtCreeUneMutation()
    {
        $recrue = SapeurBusiness::createRecrue($this->formulaireRecrue());

        $response = $this->json('POST', "/api/v2/recrues/{$recrue->id}/valider", [
            'incorporation' => '2026-01-01',
        ]);

        $response->assertStatus(200);

        $recrue->refresh();
        $this->assertEquals(SapeurBusiness::TYPE_SAPEUR, $recrue->type);
        $this->assertTrue((bool) $recrue->actif);
        $this->assertEquals('2026', $recrue->annee_incorporation);

        $mutationsResponse = $this->json('GET', "/api/v2/sapeurs/{$recrue->id}/mutations");
        $this->assertCount(1, $mutationsResponse->json('data'));
    }

    public function testValiderRefuseUnSapeurQuiNestPasUneRecrue()
    {
        $sapeur = Sapeur::factory()->create(['localite_id' => $this->localiteId]);

        $response = $this->json('POST', "/api/v2/recrues/{$sapeur->id}/valider", [
            'incorporation' => '2026-01-01',
        ]);

        $response->assertStatus(404);
    }

    public function testRejeterSupprimeLaRecrueEtSesTelephones()
    {
        $recrue = SapeurBusiness::createRecrue($this->formulaireRecrue());
        $this->assertCount(1, SapeurTelephone::where('sapeur_id', $recrue->id)->get());

        $response = $this->json('DELETE', "/api/v2/sapeurs/{$recrue->id}");

        $response->assertStatus(200);
        $this->assertNull(Sapeur::find($recrue->id));
        $this->assertCount(0, SapeurTelephone::where('sapeur_id', $recrue->id)->get());
    }

    // --- Filtre type sur /sapeurs ---

    public function testIndexSapeursFiltreParType()
    {
        $sapeur = Sapeur::factory()->create(['localite_id' => $this->localiteId, 'type' => SapeurBusiness::TYPE_SAPEUR]);
        $recrue = SapeurBusiness::createRecrue($this->formulaireRecrue());

        $response = $this->json('GET', '/api/v2/sapeurs?type[]=2');

        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($recrue->id, $ids);
        $this->assertNotContains($sapeur->id, $ids);
    }
}
