<?php

namespace Tests\Feature;

use App\Models\AspsmsParam;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AspsmsParamTest extends TestCase
{
    use DatabaseTransactions;

    private function createParams(): void
    {
        AspsmsParam::query()->delete();
        AspsmsParam::create([
            'username' => Crypt::encryptString('compte'),
            'password' => Crypt::encryptString('secret'),
            'origin' => 'GestSIS',
        ]);
    }

    public function testIndexReturnsCredit(): void
    {
        $this->createParams();
        Http::fake(['json.aspsms.com/*' => Http::response(['StatusCode' => '1', 'Credits' => '42'])]);

        $response = $this->json('GET', '/api/v2/aspsms/param');

        $response->assertStatus(200)
            ->assertJsonPath('data.username', 'compte')
            ->assertJsonPath('data.credit', '42')
            ->assertJsonMissingPath('data.credit_error');
    }

    // Un compte ASPSMS invalide ne doit pas empêcher d'ouvrir les paramètres pour le corriger
    public function testIndexReturnsParamsWhenCreditCheckFails(): void
    {
        $this->createParams();
        Http::fake(['json.aspsms.com/*' => Http::response(['StatusCode' => '3'])]);

        $response = $this->json('GET', '/api/v2/aspsms/param');

        $response->assertStatus(200)
            ->assertJsonPath('data.username', 'compte')
            ->assertJsonPath('data.credit', null)
            ->assertJsonPath('data.credit_error', 'Informations de connexion invalides');
    }

    public function testStoreRejectsInvalidAccount(): void
    {
        Http::fake(['json.aspsms.com/*' => Http::response(['StatusCode' => '3'])]);

        $response = $this->json('POST', '/api/v2/aspsms/param', [
            'username' => 'compte',
            'password' => 'mauvais',
        ]);

        $response->assertStatus(422)->assertJsonPath('message', 'Informations de connexion invalides');
    }
}
