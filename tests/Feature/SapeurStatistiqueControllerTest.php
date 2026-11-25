<?php

namespace Tests\Feature;

use App\Models\ExerciceComptable;
use App\Models\Localite;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurStatistiqueControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testStatistiqueLocaliteCompteLesSapeursSansLesPeriodesCiviles(): void
    {
        $annee = (int) now()->format('Y');
        $exerciceComptable = ExerciceComptable::create([
            'annee' => $annee,
            'designation' => (string) $annee,
            'debut' => "$annee-01-01",
            'fin' => "$annee-12-31",
            'boucle' => false,
        ]);
        $localite = Localite::create(['commune_id' => null, 'npa' => '9999', 'designation' => 'Statistique Test']);
        Sapeur::factory()->create(['localite_id' => $localite->id]);
        Sapeur::factory()->civil()->create(['localite_id' => $localite->id]);

        $response = $this->json('GET', "/api/v2/statistiques/{$exerciceComptable->id}/localite");

        $response->assertStatus(200);
        $ligne = collect($response->json('data'))->firstWhere('localite_id', $localite->id);
        $this->assertSame(1, $ligne['nb']);
    }
}
