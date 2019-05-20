<?php

namespace Tests\Feature;

use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SapeurTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUpdate()
    {
        $id = 1;
        $data = [
            'nom' => Str::random(10),
            'prenom' => Str::random(10),
            'suffixe' => '',
            'rue' => Str::random(7),
            'no_rue' => '12',
            'no_avs' => '756.5634.1212.12',
            'profession' => 'Artisan',
            'employeur' => 'Canton du Jura',
            'lieu_de_travail' => 'Delémont',

            'email' => Str::random(10).'@gmail.com',
            'actif' => 1,

            'iban' => 'CH65 82000 53636 75756 7',
            'iban_status' => 1,
            'remarque' => 'Diverses remarques',
            'porteur' => 1,
        ];

        $sapeur = SapeurBusiness::get($id);
        $sapeur->update($data);

        $sapeur = Sapeur::find($id)->firstOrFail();

        foreach ($data as $key => $value) {
            $this->assertTrue($sapeur[$key] === $value);
        }
    }
}
