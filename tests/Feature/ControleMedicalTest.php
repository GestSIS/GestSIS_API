<?php

namespace Tests\Feature;

use App\Models\ControleMedical;
use App\Models\Medecin;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ControleMedicalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function testIndexControlesMedicaux()
    {
        // Préparation
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();
        ControleMedical::factory()->create([
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id
        ]);

        // TEST
        $response = $this->json('GET', '/api/v2/controles-medicaux');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'designation', 'consultation', 'accepter', 'en_cours']
                ]
            ]);
    }

    public function testShowControleMedical()
    {
        // Préparation
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();
        $controle = ControleMedical::factory()->create([
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id
        ]);

        // TEST
        $response = $this->json('GET', '/api/v2/controles-medicaux/' . $controle->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'consultation',
                    'validite',
                    'accepter',
                    'en_cours',
                    'medecin_id',
                    'controle_medical_type_id'
                ]
            ]);
    }

    public function testCreateControleMedical()
    {
        // Préparation
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();

        $data = [
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id,
            'controle_medical_type_id' => 1,
            'consultation' => now()->format('Y-m-d'),
            'validite' => now()->addYears(2)->format('Y-m-d'),
            'designation' => 'Test Controle',
            'en_cours' => true,
            'accepter' => true
        ];

        // TEST
        $response = $this->json('POST', '/api/v2/controles-medicaux', $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'consultation',
                    'validite',
                    'accepter',
                    'en_cours'
                ]
            ]);

        $controle = $response->getData()->data;
        $this->assertEquals($data['designation'], $controle->designation);
        $this->assertEquals($data['accepter'], $controle->accepter);
        $this->assertEquals($data['en_cours'], $controle->en_cours);
    }

    public function testCreateControleMedicalWithoutDesignation()
    {
        // TEST - designation est optionnel
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();

        $data = [
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id,
            'controle_medical_type_id' => 1,
            'consultation' => now()->format('Y-m-d'),
            'en_cours' => true,
            'accepter' => true
        ];

        $response = $this->json('POST', '/api/v2/controles-medicaux', $data);

        $response->assertStatus(200);
        $controle = $response->getData()->data;
        $this->assertEquals('', $controle->designation);
    }

    public function testUpdateControleMedical()
    {
        // Préparation
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();
        $controle = ControleMedical::factory()->create([
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id,
            'designation' => 'Original',
            'accepter' => false
        ]);

        $updateData = [
            'id' => $controle->id,
            'medecin_id' => $medecin->id,
            'controle_medical_type_id' => 1,
            'consultation' => now()->format('Y-m-d'),
            'validite' => now()->addYears(3)->format('Y-m-d'),
            'designation' => 'Updated Controle',
            'en_cours' => false,
            'accepter' => true
        ];

        // TEST
        $response = $this->json('PUT', '/api/v2/controles-medicaux/' . $controle->id, $updateData);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'consultation',
                    'validite',
                    'accepter',
                    'en_cours'
                ]
            ]);

        $updatedControle = $response->getData()->data;
        $this->assertEquals('Updated Controle', $updatedControle->designation);
        $this->assertEquals(true, $updatedControle->accepter);
        // en_cours est forcé à true dans le Business
        $this->assertEquals(true, $updatedControle->en_cours);
    }

    public function testDeleteControleMedical()
    {
        // Préparation
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();
        $controle = ControleMedical::factory()->create([
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id
        ]);

        // TEST
        $response = $this->json('DELETE', '/api/v2/controles-medicaux/' . $controle->id);

        $response
            ->assertStatus(200)
            ->assertJson(['data' => 'success']);

        // Vérifier que le contrôle a été supprimé
        $this->assertDatabaseMissing('controles_medicaux', [
            'id' => $controle->id
        ]);
    }

    public function testAddJustificatif()
    {
        // Préparation
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();
        $controle = ControleMedical::factory()->create([
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id
        ]);

        $file = UploadedFile::fake()->create('justificatif.pdf', 100, 'application/pdf');

        // TEST
        $response = $this->json('POST', '/api/v2/controles-medicaux/' . $controle->id . '/justificatif', [
            'justificatif' => $file
        ], [
            'Sis-Id' => 'test_sis'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'designation']
            ]);

        // Vérifier que le contrôle a bien un fichier
        $controle = ControleMedical::find($response->getData()->data->id);
        $this->assertNotNull($controle->filename);
        $this->assertNotNull($controle->path);
    }

    public function testAddJustificatifWithoutFile()
    {
        // Préparation
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();
        $controle = ControleMedical::factory()->create([
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id
        ]);

        // TEST - Missing file
        $response = $this->json('POST', '/api/v2/controles-medicaux/' . $controle->id . '/justificatif', []);

        $response
            ->assertStatus(200)
            ->assertJson(['error' => 'Fichier justificatif manquant']);
    }

    public function testGetJustificatif()
    {
        // Préparation
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();
        $controle = ControleMedical::factory()->create([
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id
        ]);

        // Ajouter un justificatif
        $file = UploadedFile::fake()->create('justificatif.pdf', 100, 'application/pdf');
        $this->json('POST', '/api/v2/controles-medicaux/' . $controle->id . '/justificatif', [
            'justificatif' => $file
        ], [
            'Sis-Id' => 'test_sis'
        ]);

        // TEST - Download justificatif
        $response = $this->get('/api/v2/controles-medicaux/' . $controle->id . '/justificatif');

        $response->assertStatus(200);
        // Vérifier que c'est un téléchargement de fichier
        $this->assertNotNull($response->headers->get('content-type'));
    }

    public function testRemoveJustificatif()
    {
        // Préparation
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();
        $controle = ControleMedical::factory()->create([
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id
        ]);

        // Ajouter un justificatif
        $file = UploadedFile::fake()->create('justificatif.pdf', 100, 'application/pdf');
        $this->json('POST', '/api/v2/controles-medicaux/' . $controle->id . '/justificatif', [
            'justificatif' => $file
        ], [
            'Sis-Id' => 'test_sis'
        ]);

        // TEST - Remove justificatif
        $response = $this->json('DELETE', '/api/v2/controles-medicaux/' . $controle->id . '/justificatif');

        $response
            ->assertStatus(200)
            ->assertJson(['data' => 'success']);
    }

    public function testValiditeDateAfterConsultation()
    {
        // Préparation
        $sapeur = Sapeur::factory()->create();
        $medecin = Medecin::factory()->create();

        // TEST - validite après consultation
        $data = [
            'sapeur_id' => $sapeur->id,
            'medecin_id' => $medecin->id,
            'controle_medical_type_id' => 1,
            'consultation' => now()->format('Y-m-d'),
            'validite' => now()->addYear()->format('Y-m-d'), // Date après consultation
            'designation' => 'Test Valid After Date',
            'en_cours' => true,
            'accepter' => true
        ];

        $response = $this->json('POST', '/api/v2/controles-medicaux', $data);

        $response->assertStatus(200);
    }
}
