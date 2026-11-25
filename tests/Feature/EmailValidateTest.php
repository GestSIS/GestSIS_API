<?php

namespace Tests\Feature;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Tests\TestCase;

class EmailValidateTest extends TestCase
{
    protected array $createdIds = [];

    protected function tearDown(): void
    {
        foreach ($this->createdIds as [$db, $id]) {
            Sapeur::on($db)->where('id', $id)->delete();
        }

        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function createSapeur(string $db, string $email, array $attributes = []): Sapeur
    {
        $sapeur = Sapeur::on($db)->create(array_merge(
            Sapeur::factory()->make()->toArray(),
            ['actif' => 1, 'statut_sapeur' => SapeurBusiness::STATUT_ACTIF, 'statut_civil' => SapeurBusiness::STATUT_JAMAIS, 'email' => $email],
            $attributes,
        ));

        $this->createdIds[] = [$db, $sapeur->id];

        return $sapeur;
    }

    protected function validateEmail(string $email): array
    {
        return $this->json('GET', '/api/v2/email-validate', ['email' => $email])
            ->assertStatus(200)
            ->json('data');
    }

    public function testValidateEmailRetourneLeSapeurDuSisPourUnEmailIdentique(): void
    {
        $sapeur = $this->createSapeur('db_hs', 'jean.dupont@example-validate.ch');

        $this->assertSame(['hs' => $sapeur->id], $this->validateEmail('jean.dupont@example-validate.ch'));
    }

    public function testValidateEmailIgnoreLaCasse(): void
    {
        $sapeur = $this->createSapeur('db_hs', 'Jean.Dupont@Example-Validate.ch');

        $this->assertSame(['hs' => $sapeur->id], $this->validateEmail('jean.dupont@example-validate.ch'));
    }

    public function testValidateEmailRefuseUnDomaineHomographeAccentue(): void
    {
        $this->createSapeur('db_hs', 'jean.dupont@example-validate.ch');

        $this->assertSame([], $this->validateEmail('jean.dupont@exämple-validate.ch'));
    }

    public function testValidateEmailRefuseUnePartieLocaleAccentuee(): void
    {
        $this->createSapeur('db_hs', 'andre.dupont@example-validate.ch');

        $this->assertSame([], $this->validateEmail('andré.dupont@example-validate.ch'));
    }

    public function testValidateEmailTrouveLeSapeurExactMalgreUnHomographeEnregistreAvant(): void
    {
        $this->createSapeur('db_hs', 'andré.dupont@example-validate.ch');
        $exact = $this->createSapeur('db_hs', 'andre.dupont@example-validate.ch');

        $this->assertSame(['hs' => $exact->id], $this->validateEmail('andre.dupont@example-validate.ch'));
    }

    public function testValidateEmailRetourneUnCivilActif(): void
    {
        $civil = $this->createSapeur('db_hs', 'civil@example-validate.ch', [
            'statut_sapeur' => SapeurBusiness::STATUT_JAMAIS,
            'statut_civil' => SapeurBusiness::STATUT_ACTIF,
        ]);

        $this->assertSame(['hs' => $civil->id], $this->validateEmail('civil@example-validate.ch'));
    }

    public function testValidateEmailIgnoreUnAncienSapeurInactif(): void
    {
        $this->createSapeur('db_hs', 'ancien@example-validate.ch', [
            'statut_sapeur' => SapeurBusiness::STATUT_INACTIF,
        ]);

        $this->assertSame([], $this->validateEmail('ancien@example-validate.ch'));
    }

    public function testValidateEmailIgnoreUneRecrue(): void
    {
        $this->createSapeur('db_hs', 'recrue@example-validate.ch', [
            'statut_sapeur' => SapeurBusiness::STATUT_JAMAIS,
        ]);

        $this->assertSame([], $this->validateEmail('recrue@example-validate.ch'));
    }
}
