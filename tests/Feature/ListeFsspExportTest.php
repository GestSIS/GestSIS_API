<?php

namespace Tests\Feature;

use App\Collections\ListeFsspExport;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ListeFsspExportTest extends TestCase
{
    use DatabaseTransactions;

    public function testListeContientLesSapeursEnServiceSansLesCivils(): void
    {
        $sapeur = Sapeur::factory()->create(['nom' => 'SapeurFsspTest']);
        $civil = Sapeur::factory()->civil()->create(['nom' => 'CivilFsspTest']);
        // Ancien sapeur, civil actif : sa période civile ne compte pas comme du service
        $ancienSapeurCivil = Sapeur::factory()->ancienSapeurCivil()->create(['nom' => 'AncienFsspTest']);

        $noms = (new ListeFsspExport(now()->toDateString()))->collection()->pluck('nom');

        $this->assertContains($sapeur->nom, $noms);
        $this->assertNotContains($civil->nom, $noms);
        $this->assertNotContains($ancienSapeurCivil->nom, $noms);
    }
}
