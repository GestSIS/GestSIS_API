<?php

namespace Tests\Feature;

use App\Collections\ListeFoadExport;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ListeFoadExportTest extends TestCase
{
    use DatabaseTransactions;

    public function testListeContientLesSapeursEnServiceSansLesCivils(): void
    {
        $sapeur = Sapeur::factory()->create(['nom' => 'SapeurFoadTest']);
        $civil = Sapeur::factory()->civil()->create(['nom' => 'CivilFoadTest']);
        // Ancien sapeur, civil actif : sa période civile ne compte pas comme du service
        $ancienSapeurCivil = Sapeur::factory()->ancienSapeurCivil()->create(['nom' => 'AncienFoadTest']);

        $noms = (new ListeFoadExport(now()->toDateString()))->collection()->pluck('nom');

        $this->assertContains($sapeur->nom, $noms);
        $this->assertNotContains($civil->nom, $noms);
        $this->assertNotContains($ancienSapeurCivil->nom, $noms);
    }
}
