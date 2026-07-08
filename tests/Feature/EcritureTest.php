<?php

namespace Tests\Feature;

use App\Domaine\Business\ImputationBusiness;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EcritureTest extends TestCase
{
    use DatabaseTransactions;

    private function insertEcriture(int $module, int $exerciceComptableId = 2): int
    {
        return DB::table('ecritures')->insertGetId([
            'designation' => 'test ecriture',
            'total' => 10,
            'tarif' => 10,
            'quantite' => 1,
            'type_unite_id' => ImputationBusiness::UNITE_PIECE,
            'module' => $module,
            'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
            'sapeur_id' => 1,
            'compte_id' => 1,
            'exercice_comptable_id' => $exerciceComptableId,
            'ecriture_categorie_id' => 1,
            'date' => '2025-01-15',
        ]);
    }

    private function payloadDivers(array $overrides = []): array
    {
        return array_merge([
            'designation' => 'ecriture modifiee',
            'total' => 10,
            'tarif' => 10,
            'quantite' => 1,
            'type_unite_id' => ImputationBusiness::UNITE_PIECE,
            'date' => '2025-01-15',
            'sapeur_id' => 1,
            'exercice_comptable_id' => 2,
            'compte_id' => 1,
            'ecriture_categorie_id' => 1,
            'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
            'module' => ImputationBusiness::ECRITURE_MODULE_DIVERS,
        ], $overrides);
    }

    /**
     * Une écriture générée par un autre module (intervention, AVS, ...) ne peut pas être modifiée
     */
    public function testModifierEcritureModuleNonDiversRefuse()
    {
        $ecritureId = $this->insertEcriture(ImputationBusiness::ECRITURE_MODULE_INTERVENTION);

        $response = $this->json('PUT', "api/v2/ecritures/{$ecritureId}", $this->payloadDivers());

        $response
            ->assertStatus(200)
            ->assertJsonPath('error.message', 'Seules les écritures du module divers peuvent être modifiées');

        $this->assertDatabaseHas('ecritures', ['id' => $ecritureId, 'designation' => 'test ecriture']);
    }

    /**
     * Une écriture générée par un autre module ne peut pas être supprimée
     */
    public function testSupprimerEcritureModuleNonDiversRefuse()
    {
        $ecritureId = $this->insertEcriture(ImputationBusiness::ECRITURE_MODULE_INTERVENTION);

        $response = $this->json('DELETE', "api/v2/ecritures/{$ecritureId}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('error.message', 'Seules les écritures du module divers peuvent être supprimées');

        $this->assertDatabaseHas('ecritures', ['id' => $ecritureId]);
    }

    /**
     * Une écriture ne peut pas être déplacée vers un exercice comptable clôturé
     */
    public function testModifierEcritureVersExerciceClotureRefuse()
    {
        $exerciceClotureId = DB::table('exercice_comptables')->insertGetId([
            'annee' => 2098,
            'designation' => 'exercice cloture',
            'debut' => '2098-01-01',
            'fin' => '2098-12-31',
            'boucle' => 1,
        ]);
        $ecritureId = $this->insertEcriture(ImputationBusiness::ECRITURE_MODULE_DIVERS);

        $response = $this->json(
            'PUT',
            "api/v2/ecritures/{$ecritureId}",
            $this->payloadDivers(['exercice_comptable_id' => $exerciceClotureId])
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('error.message', "Exercice comptable clôturé, impossible d'effectuer cette action");

        $this->assertDatabaseHas('ecritures', ['id' => $ecritureId, 'exercice_comptable_id' => 2]);
    }

    /**
     * Une écriture divers peut être modifiée puis supprimée normalement
     */
    public function testModifierEtSupprimerEcritureDivers()
    {
        $ecritureId = $this->insertEcriture(ImputationBusiness::ECRITURE_MODULE_DIVERS);

        $response = $this->json('PUT', "api/v2/ecritures/{$ecritureId}", $this->payloadDivers());
        $response
            ->assertStatus(200)
            ->assertJson(['data' => ['designation' => 'ecriture modifiee']]);

        $response = $this->json('DELETE', "api/v2/ecritures/{$ecritureId}");
        $response
            ->assertStatus(200)
            ->assertJson(['data' => 'ok']);

        $this->assertDatabaseMissing('ecritures', ['id' => $ecritureId]);
    }
}
