<?php

namespace Tests\Feature;

use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\IcsToken;
use App\Models\Sapeur;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IcsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test qu'un token valide retourne un flux ICS contenant les exercices du sapeur.
     *
     * @return void
     * @throws Exception
     */
    public function testIcsShowWithValidTokenOk()
    {
        $sapeur = Sapeur::factory()->create();
        $icsToken = IcsToken::factory()->create(['sapeur_id' => $sapeur->id]);

        $exercice = Exercice::factory()->create([
            'date' => now()->addWeek()->format('Y-m-d'),
            'heure' => '19:00:00',
        ]);
        ExerciceSapeur::factory()->create([
            'sapeur_id' => $sapeur->id,
            'exercice_id' => $exercice->id,
            'excuse_statut' => 0,
            'date_demande' => now(),
            'remarque' => '',
            'justificatif_path' => '',
            'justificatif_filename' => '',
            'justification' => '',
        ]);

        $response = $this->get("/api/v2/ics/test/{$icsToken->token}");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        $this->assertStringContainsString("exercice-{$exercice->id}@gestsis", $response->getContent());
    }

    /**
     * Test qu'un token inconnu renvoie une 404.
     *
     * @return void
     * @throws Exception
     */
    public function testIcsShowWithInvalidTokenNotFound()
    {
        $response = $this->get('/api/v2/ics/test/invalid-token');

        $response->assertStatus(404);
    }
}
