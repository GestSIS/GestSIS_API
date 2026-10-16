<?php

namespace Tests\Feature;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Tests\TestCase;

class IdentitesActivesTest extends TestCase
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

    public function testListeIdentitesActivesRegroupeParSisFiltreTypeEtActifAvecEmailNullable(): void
    {
        $actifHsAvecEmail = $this->createSapeur('db_hs', ['actif' => 1, 'type' => SapeurBusiness::TYPE_SAPEUR, 'email' => 'actif-hs@example.com']);
        $actifHsSansEmail = $this->createSapeur('db_hs', ['actif' => 1, 'type' => SapeurBusiness::TYPE_SAPEUR, 'email' => '']);
        $inactifHs = $this->createSapeur('db_hs', ['actif' => 0, 'type' => SapeurBusiness::TYPE_SAPEUR, 'email' => 'inactif-hs@example.com']);
        $recrueActive = $this->createSapeur('db_hs', ['actif' => 1, 'type' => SapeurBusiness::TYPE_RECRUE, 'email' => 'recrue@example.com']);
        $civilTest = $this->createSapeur('db_test', ['actif' => 1, 'type' => SapeurBusiness::TYPE_CIVIL, 'email' => 'civil-test@example.com']);

        $response = $this->json('GET', '/api/v2/identites-actives');

        $response->assertStatus(200)->assertJsonStructure(['data' => ['hs', 'test']]);
        $hs = $response->json('data.hs');
        $test = $response->json('data.test');

        $this->assertSame('actif-hs@example.com', $hs[$actifHsAvecEmail->id]);
        $this->assertArrayHasKey($actifHsSansEmail->id, $hs);
        $this->assertArrayNotHasKey($inactifHs->id, $hs);
        $this->assertArrayNotHasKey($recrueActive->id, $hs);
        $this->assertSame('civil-test@example.com', $test[$civilTest->id]);
    }
}
