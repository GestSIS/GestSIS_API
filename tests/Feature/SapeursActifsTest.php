<?php

namespace Tests\Feature;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Tests\TestCase;

class SapeursActifsTest extends TestCase
{
    protected array $createdIds = [];

    protected function tearDown(): void
    {
        foreach ($this->createdIds as [$db, $id]) {
            Sapeur::on($db)->where('id', $id)->delete();
        }

        parent::tearDown();
    }

    protected function createSapeur(string $db, array $attributes): Sapeur
    {
        $sapeur = Sapeur::on($db)->create(array_merge(
            Sapeur::factory()->make()->toArray(),
            $attributes
        ));

        $this->createdIds[] = [$db, $sapeur->id];

        return $sapeur;
    }

    public function testListeSapeursActifsRegroupeParSisEtFiltreLesInactifs()
    {
        $actifHs = $this->createSapeur('db_hs', ['actif' => 1, 'type' => SapeurBusiness::TYPE_SAPEUR]);
        $inactifHs = $this->createSapeur('db_hs', ['actif' => 0, 'type' => SapeurBusiness::TYPE_SAPEUR]);
        $actifTest = $this->createSapeur('db_test', ['actif' => 1, 'type' => SapeurBusiness::TYPE_CIVIL]);
        $recrueActive = $this->createSapeur('db_hs', ['actif' => 1, 'type' => SapeurBusiness::TYPE_RECRUE]);

        $response = $this->json('GET', '/api/v2/sapeurs-actifs');

        $response->assertStatus(200)->assertJsonStructure(['data' => ['hs', 'test']]);

        $hsIds = $response->json('data.hs');
        $testIds = $response->json('data.test');

        $this->assertContains($actifHs->id, $hsIds);
        $this->assertNotContains($inactifHs->id, $hsIds);
        $this->assertNotContains($recrueActive->id, $hsIds);
        $this->assertContains($actifTest->id, $testIds);
    }
}
