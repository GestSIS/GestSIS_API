<?php

namespace Tests\Feature;

use App\Collections\SapeursExport;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeursExportTest extends TestCase
{
    use DatabaseTransactions;

    public function testExportParDefautContientLesSapeursActifsSansLesCivils(): void
    {
        $sapeur = Sapeur::factory()->create(['nom' => 'SapeurExportTest']);
        $civil = Sapeur::factory()->civil()->create(['nom' => 'CivilExportTest']);
        $ancienSapeurCivil = Sapeur::factory()->ancienSapeurCivil()->create(['nom' => 'AncienCivilExportTest']);
        $ancienSapeur = Sapeur::factory()->inactif()->create(['nom' => 'AncienExportTest']);

        $noms = (new SapeursExport([]))->collection()->pluck('nom');

        $this->assertContains($sapeur->nom, $noms);
        $this->assertNotContains($civil->nom, $noms);
        $this->assertNotContains($ancienSapeurCivil->nom, $noms);
        $this->assertNotContains($ancienSapeur->nom, $noms);
    }

    public function testColonneActifVautUnPourUnSapeurOuUnCivilActif(): void
    {
        $sapeur = Sapeur::factory()->create(['nom' => 'SapeurActifExportTest']);
        $civil = Sapeur::factory()->civil()->create(['nom' => 'CivilActifExportTest']);
        $ancienSapeurCivil = Sapeur::factory()->ancienSapeurCivil()->create(['nom' => 'AncienCivilActifExportTest']);
        $ancienSapeur = Sapeur::factory()->inactif()->create(['nom' => 'AncienActifExportTest']);
        $recrue = Sapeur::factory()->recrue()->create(['nom' => 'RecrueActifExportTest']);
        $ids = [$sapeur->id, $civil->id, $ancienSapeurCivil->id, $ancienSapeur->id, $recrue->id];

        $actifs = (new SapeursExport($ids))->collection()->pluck('actif', 'nom');

        $this->assertSame(1, (int) $actifs[$sapeur->nom]);
        $this->assertSame(1, (int) $actifs[$civil->nom]);
        $this->assertSame(1, (int) $actifs[$ancienSapeurCivil->nom]);
        $this->assertSame(0, (int) $actifs[$ancienSapeur->nom]);
        $this->assertSame(0, (int) $actifs[$recrue->nom]);
    }
}
