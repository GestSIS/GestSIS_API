<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\MaterielTypeBusiness;
use App\Models\Article;
use App\Models\BatterieType;
use App\Models\Intervention;
use App\Models\InterventionVehicule;
use App\Models\MaterielCategorie;
use App\Models\MaterielType;
use App\Models\TuyauDiametre;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MaterielTypeTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return array<string, mixed>
     */
    private function basePayload(int $type, array $extra = []): array
    {
        $categorie = MaterielCategorie::factory()->create();

        return array_merge([
            'designation' => 'Test type',
            'materiel_categorie_id' => $categorie->id,
            'type' => $type,
            'est_numerote' => false,
            'est_attribuable' => false,
            'est_taillee' => false,
            'est_lavable' => false,
        ], $extra);
    }

    public function testStorePipeTypeCreatesTuyauRow(): void
    {
        $diametre = TuyauDiametre::firstOrCreate(['diametre' => 55]);

        $payload = $this->basePayload(MaterielTypeBusiness::TYPE_PIPE, [
            'tuyau' => ['tuyau_diametre_id' => $diametre->id, 'longeur' => 20, 'separement' => true],
        ]);

        $response = $this->json('POST', '/api/v2/materiel-types', $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $id = $response->json('data.id');
        $this->assertDatabaseHas('materiel_type_tuyaux', [
            'id' => $id,
            'tuyau_diametre_id' => $diametre->id,
            'longeur' => 20,
        ]);
    }

    public function testStoreCastsTypeSentAsStringSoBatterieBranchTriggers(): void
    {
        $batterieType = BatterieType::firstOrCreate(['nom' => 'AA']);

        // type envoyé en string "2" : sans cast la comparaison stricte === TYPE_BATTERY échouerait
        $payload = $this->basePayload(MaterielTypeBusiness::TYPE_BATTERY, [
            'type' => (string) MaterielTypeBusiness::TYPE_BATTERY,
            'batterie' => ['nombre' => 3, 'batterie_type_id' => $batterieType->id],
        ]);

        $response = $this->json('POST', '/api/v2/materiel-types', $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $id = $response->json('data.id');
        $this->assertDatabaseHas('materiel_type_batteries', [
            'id' => $id,
            'nombre' => 3,
            'batterie_type_id' => $batterieType->id,
        ]);
    }

    public function testUpdateBatterieTypeTwiceDoesNotDuplicateRow(): void
    {
        $batterieType = BatterieType::firstOrCreate(['nom' => 'AAA']);
        $type = MaterielType::factory()->create([
            'materiel_categorie_id' => MaterielCategorie::factory()->create()->id,
        ]);

        $payload = [
            'designation' => 'Lampe',
            'materiel_categorie_id' => $type->materiel_categorie_id,
            'type' => MaterielTypeBusiness::TYPE_BATTERY,
            'est_numerote' => false,
            'est_attribuable' => false,
            'est_taillee' => false,
            'est_lavable' => false,
            'batterie' => ['nombre' => 2, 'batterie_type_id' => $batterieType->id],
        ];

        $this->json('PUT', "/api/v2/materiel-types/{$type->id}", $payload, ['Sis-Key' => 1])->assertStatus(200);
        $this->json('PUT', "/api/v2/materiel-types/{$type->id}", array_merge($payload, [
            'batterie' => ['nombre' => 5, 'batterie_type_id' => $batterieType->id],
        ]), ['Sis-Key' => 1])->assertStatus(200);

        // updateOrCreate : une seule ligne, mise à jour (pas de doublon comme avec insert())
        $this->assertSame(1, MaterielType::find($type->id)->batterie()->count());
        $this->assertDatabaseHas('materiel_type_batteries', ['id' => $type->id, 'nombre' => 5]);
    }

    public function testStoreVehiculeTypePersistsTypeAndEstEmplacementImmediately(): void
    {
        $payload = $this->basePayload(MaterielTypeBusiness::TYPE_VEHICULE);

        $response = $this->json('POST', '/api/v2/materiel-types', $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $id = $response->json('data.id');
        // Régression du bug $fillable : sans 'type' dans le fillable, cette colonne
        // restait à 0 après la création (seul editProduct() fonctionnait).
        $this->assertDatabaseHas('materiel_types', [
            'id' => $id,
            'type' => MaterielTypeBusiness::TYPE_VEHICULE,
            'est_emplacement' => true,
        ]);
    }

    public function testStoreVehiculeTypeForcesEstAttribuableEstLavableEtEstTailleeToFalse(): void
    {
        $payload = $this->basePayload(MaterielTypeBusiness::TYPE_VEHICULE, [
            'est_attribuable' => true,
            'est_lavable' => true,
            'est_taillee' => true,
        ]);

        $response = $this->json('POST', '/api/v2/materiel-types', $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('materiel_types', [
            'id' => $response->json('data.id'),
            'est_attribuable' => false,
            'est_lavable' => false,
            'est_taillee' => false,
        ]);
    }

    public function testUpdateVehiculeTypeForcesEstAttribuableEstLavableEtEstTailleeToFalse(): void
    {
        $categorie = MaterielCategorie::factory()->create();
        $type = MaterielType::factory()->create([
            'materiel_categorie_id' => $categorie->id,
            'type' => MaterielTypeBusiness::TYPE_NONE,
            'est_emplacement' => false,
        ]);

        $payload = [
            'designation' => $type->designation,
            'materiel_categorie_id' => $categorie->id,
            'type' => MaterielTypeBusiness::TYPE_VEHICULE,
            'est_numerote' => false,
            'est_attribuable' => true,
            'est_taillee' => true,
            'est_lavable' => true,
        ];

        $response = $this->json('PUT', "/api/v2/materiel-types/{$type->id}", $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('materiel_types', [
            'id' => $type->id,
            'est_attribuable' => false,
            'est_lavable' => false,
            'est_taillee' => false,
        ]);
    }

    public function testStoreStandardTypeEstEmplacementIsFalse(): void
    {
        $payload = $this->basePayload(MaterielTypeBusiness::TYPE_NONE);

        $response = $this->json('POST', '/api/v2/materiel-types', $payload, ['Sis-Key' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('materiel_types', [
            'id' => $response->json('data.id'),
            'est_emplacement' => false,
        ]);
    }

    public function testCannotRemoveVehiculeTypeWhenInterventionLinked(): void
    {
        $categorie = MaterielCategorie::factory()->create();
        $type = MaterielType::factory()->create([
            'materiel_categorie_id' => $categorie->id,
            'type' => MaterielTypeBusiness::TYPE_VEHICULE,
            'est_emplacement' => true,
        ]);
        $article = Article::factory()->create(['materiel_type_id' => $type->id]);
        $intervention = Intervention::factory()->create();
        InterventionVehicule::create([
            'intervention_id' => $intervention->id,
            'vehicule_id' => $article->id,
        ]);

        $payload = [
            'designation' => $type->designation,
            'materiel_categorie_id' => $categorie->id,
            'type' => MaterielTypeBusiness::TYPE_NONE,
            'est_numerote' => false,
            'est_attribuable' => false,
            'est_taillee' => false,
            'est_lavable' => false,
        ];

        $response = $this->json('PUT', "/api/v2/materiel-types/{$type->id}", $payload, ['Sis-Key' => 1]);

        // Doit être une erreur métier propre (jointure sur le bon nom de table),
        // pas une erreur SQL sur "intervention_vehicules" (qui n'existe pas).
        $response->assertStatus(200);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('materiel_types', ['id' => $type->id, 'type' => MaterielTypeBusiness::TYPE_VEHICULE]);
    }
}
