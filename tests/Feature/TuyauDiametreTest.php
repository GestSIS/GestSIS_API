<?php

namespace Tests\Feature;

use App\Models\TuyauDiametre;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TuyauDiametreTest extends TestCase
{
    use DatabaseTransactions;

    public function testStoreDuplicateDiametreIsRejected(): void
    {
        $diametre = TuyauDiametre::firstOrCreate(['diametre' => 75]);
        $countBefore = TuyauDiametre::where('diametre', 75)->count();

        $response = $this->json('POST', '/api/v2/tuyau-diametres', [
            'diametre' => $diametre->diametre,
        ], ['Sis-Key' => 1]);

        // L'app renvoie les erreurs de validation en 200 avec une clé "error"
        $response->assertOk();
        $response->assertJsonStructure(['error' => ['diametre']]);
        // pas de doublon inséré (plus de 500 sur contrainte unique)
        $this->assertSame($countBefore, TuyauDiametre::where('diametre', 75)->count());
    }

    public function testStoreNewDiametreSucceeds(): void
    {
        // un diamètre improbable pour ne pas heurter les données seedées
        $value = 987;
        TuyauDiametre::where('diametre', $value)->delete();

        $response = $this->json('POST', '/api/v2/tuyau-diametres', [
            'diametre' => $value,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tuyau_diametres', ['diametre' => $value]);
    }

    public function testUpdateKeepingSameDiametreIsAllowed(): void
    {
        $diametre = TuyauDiametre::firstOrCreate(['diametre' => 75]);

        $response = $this->json('PUT', "/api/v2/tuyau-diametres/{$diametre->id}", [
            'diametre' => $diametre->diametre,
        ], ['Sis-Key' => 1]);

        $response->assertStatus(200);
    }
}
