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
     * Test que l'heure de l'exercice est exportée en heure locale suisse (TZID), pas convertie en UTC.
     *
     * @return void
     * @throws Exception
     */
    public function testIcsShowUsesSwissLocalTime()
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
        $content = $response->getContent();

        $response->assertStatus(200);
        $this->assertStringContainsString('TZID:Europe/Zurich', $content);
        $this->assertMatchesRegularExpression(
            '/DTSTART;TZID=Europe\/Zurich:\d{8}T190000/',
            $content,
        );
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

    /**
     * Test qu'un exercice où le sapeur n'est pas convoqué (juste ajouté) apparaît quand même,
     * marqué comme "pour info" et en statut tentative/transparent.
     *
     * @return void
     * @throws Exception
     */
    public function testIcsShowMarksNonConvoqueExerciceAsPourInfo()
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
            'convoque' => 0,
            'excuse_statut' => 0,
            'date_demande' => now(),
            'remarque' => '',
            'justificatif_path' => '',
            'justificatif_filename' => '',
            'justification' => '',
        ]);

        $response = $this->get("/api/v2/ics/test/{$icsToken->token}");

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString($exercice->designation . ' - pour info', $content);
        $this->assertStringContainsString('STATUS:TENTATIVE', $content);
        $this->assertStringContainsString('TRANSP:TRANSPARENT', $content);
    }

    /**
     * Test qu'un exercice annulé (statut 0) reste dans le flux mais marqué comme annulé.
     *
     * @return void
     * @throws Exception
     */
    public function testIcsShowMarksCancelledExerciceAsAnnule()
    {
        $sapeur = Sapeur::factory()->create();
        $icsToken = IcsToken::factory()->create(['sapeur_id' => $sapeur->id]);

        $exercice = Exercice::factory()->create([
            'date' => now()->addWeek()->format('Y-m-d'),
            'heure' => '19:00:00',
            'statut' => 0,
        ]);
        ExerciceSapeur::factory()->create([
            'sapeur_id' => $sapeur->id,
            'exercice_id' => $exercice->id,
            'convoque' => 1,
            'excuse_statut' => 0,
            'date_demande' => now(),
            'remarque' => '',
            'justificatif_path' => '',
            'justificatif_filename' => '',
            'justification' => '',
        ]);

        $response = $this->get("/api/v2/ics/test/{$icsToken->token}");

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString($exercice->designation . ' - ANNULÉ', $content);
        $this->assertStringContainsString('STATUS:CANCELLED', $content);
        $this->assertStringContainsString('TRANSP:TRANSPARENT', $content);
    }
}
