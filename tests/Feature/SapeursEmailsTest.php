<?php

namespace Tests\Feature;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Tests\TestCase;

class SapeursEmailsTest extends TestCase
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

    public function testListeSapeursEmailsRegroupeParSisSansFiltreActif()
    {
        $actifHs = $this->createSapeur('db_hs', [
            'actif' => 1,
            'type' => SapeurBusiness::TYPE_SAPEUR,
            'email' => 'actif-hs@example.com',
        ]);
        $inactifHs = $this->createSapeur('db_hs', [
            'actif' => 0,
            'type' => SapeurBusiness::TYPE_SAPEUR,
            'email' => 'inactif-hs@example.com',
        ]);
        $recrueTest = $this->createSapeur('db_test', [
            'actif' => 1,
            'type' => SapeurBusiness::TYPE_RECRUE,
            'email' => 'recrue-test@example.com',
        ]);
        $civilTest = $this->createSapeur('db_test', [
            'actif' => 0,
            'type' => SapeurBusiness::TYPE_CIVIL,
            'email' => 'civil-test@example.com',
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs-emails');

        $response->assertStatus(200)->assertJsonStructure(['data' => ['hs', 'test']]);

        $hsEmails = $response->json('data.hs');
        $testEmails = $response->json('data.test');

        // Pas de filtre `actif` : un sapeur inactif reste dans la liste.
        $this->assertSame('actif-hs@example.com', $hsEmails[$actifHs->id]);
        $this->assertSame('inactif-hs@example.com', $hsEmails[$inactifHs->id]);

        // Filtre `type` identique à email-validate/sapeurs-actifs : les recrues sont exclues.
        $this->assertArrayNotHasKey($recrueTest->id, $testEmails);
        $this->assertSame('civil-test@example.com', $testEmails[$civilTest->id]);
    }

    public function testListeSapeursEmailsExcludesEmptyEmail()
    {
        $sapeur = $this->createSapeur('db_hs', [
            'actif' => 1,
            'type' => SapeurBusiness::TYPE_SAPEUR,
            'email' => '',
        ]);

        $response = $this->json('GET', '/api/v2/sapeurs-emails');

        $response->assertStatus(200);
        $this->assertArrayNotHasKey($sapeur->id, $response->json('data.hs'));
    }
}
