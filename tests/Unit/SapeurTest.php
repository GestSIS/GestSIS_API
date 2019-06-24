<?php

namespace Tests\Feature;

use App\Models\Sapeur;
use App\Business\SapeurBusiness;
use Exception;
use Illuminate\Support\Str;
use Tests\TestCase;

class SapeurTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     * @throws Exception
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

            'email' => Str::random(10) . '@gmail.com',
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
