<?php

namespace Tests\Feature;

use App\Models\Localite;
use App\Models\LocaliteSis;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LocaliteSisTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * La suppression renvoie la liste des localités restantes : l'APP remplace sa liste par cette réponse.
     */
    public function testDestroyLocalitesSisReturnsRemainingLocalites(): void
    {
        $keptLocalite = Localite::create(['commune_id' => null, 'npa' => '2800', 'designation' => 'Conservée']);
        $removedLocalite = Localite::create(['commune_id' => null, 'npa' => '2900', 'designation' => 'Supprimée']);
        LocaliteSis::insert([
            ['localite_id' => $keptLocalite->id],
            ['localite_id' => $removedLocalite->id],
        ]);

        $response = $this->json('DELETE', '/api/v2/localites-sis', [$removedLocalite->id]);

        $response->assertStatus(200);
        $this->assertContains($keptLocalite->id, $response->json('data'));
        $this->assertNotContains($removedLocalite->id, $response->json('data'));
    }
}
