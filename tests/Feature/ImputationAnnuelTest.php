<?php

namespace Tests\Feature;

use App\Domaine\API\ImputationService;
use App\Domaine\API\SapeurService;
use App\Domaine\Business\ImputationBusiness;
use App\Infrastructure\Models\Commune;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Models\Localite;
use App\Infrastructure\Models\Sapeur;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImputationAnnuelTest extends TestCase
{
    use DatabaseTransactions;

    protected $comptabiliteService;
    protected $sapeurOneId;
    protected $sapeurTwoId;
    protected $sapeurThreeId;
    protected $sapeurFourId;
    protected $exerciceId;
    protected $localiteId;

    protected function setUp(): void
    {
        parent::setUp();

        // Avec DatabaseTransactions, les seeders sont exécutés automatiquement
        // donc les localités, civilités, fonctions, etc. existent déjà
        $this->localiteId = 1; // Localité créée par le seeder

        $sapeurService = $this->app->make(SapeurService::class);
        $this->comptabiliteService = $this->app->make(ImputationService::class);

        // Sapeur 1 : Commandant (fonction 1) - actif toute l'année
        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['localite_id'] = $this->localiteId;
        $this->sapeurOneId = $sapeurService->createSapeur($data)->id;
        $sapeurService->addFonction($this->sapeurOneId, [
            'fonction_id' => 1,
            'debut' => "2019-01-01",
            'fin' => null,
            'remarque' => 'Commandant actif toute l\'année'
        ]);

        // Sapeur 2 : Caissier (fonction 4) - actif 6 mois (janv-juin)
        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['localite_id'] = $this->localiteId;
        $this->sapeurTwoId = $sapeurService->createSapeur($data)->id;
        $sapeurService->addFonction($this->sapeurTwoId, [
            'fonction_id' => 4,
            'debut' => "2019-01-01",
            'fin' => "2019-06-30",
            'remarque' => '6 mois d\'activité'
        ]);

        // Sapeur 3 : Fourrier (fonction 5) - actif 3 mois (mars-mai)
        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['localite_id'] = $this->localiteId;
        $this->sapeurThreeId = $sapeurService->createSapeur($data)->id;
        $sapeurService->addFonction($this->sapeurThreeId, [
            'fonction_id' => 5,
            'debut' => "2019-03-01",
            'fin' => "2019-05-31",
            'remarque' => '3 mois d\'activité'
        ]);

        // Sapeur 4 : Vice-commandant (fonction 2) - actif du 15 mars au 10 septembre (7 mois entamés)
        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['localite_id'] = $this->localiteId;
        $this->sapeurFourId = $sapeurService->createSapeur($data)->id;
        $sapeurService->addFonction($this->sapeurFourId, [
            'fonction_id' => 2,
            'debut' => "2019-03-15",
            'fin' => "2019-09-10",
            'remarque' => '7 mois partiels'
        ]);
    }

    /**
     * Test imputation annuelle avec indemnités annuelles (UNITE_AN)
     * Doit rester inchangé : 1 an = 1 année complète peu importe la période
     *
     * @return void
     * @throws Exception
     */
    public function testImputationAnnuelAvecUniteAnnee()
    {
        $exercice_comptable = ExerciceComptable::find(3);

        $response = $this->json('POST', "/api/v2/imputation/annuel/{$exercice_comptable->id}");

        $response->assertStatus(200);

        // Vérifier les écritures pour l'indemnité de type 1 (UNITE_AN = 3)
        // Sapeur 1 : Commandant avec 2000 CHF/an
        $ecriture = Ecriture::where([
            ['sapeur_id', '=', $this->sapeurOneId],
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL],
            ['type_unite_id', '=', 3] // UNITE_AN
        ])->where('tarif', 2000)->first();

        $this->assertNotNull($ecriture);
        $this->assertEquals(1, $ecriture->quantite); // Toujours 1 pour UNITE_AN
        $this->assertEquals(2000, $ecriture->total);

        // Sapeur 2 : Caissier avec 2500 CHF/an (même s'il n'a que 6 mois)
        $ecriture = Ecriture::where([
            ['sapeur_id', '=', $this->sapeurTwoId],
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL],
            ['type_unite_id', '=', 3]
        ])->where('tarif', 2500)->first();

        $this->assertNotNull($ecriture);
        $this->assertEquals(1, $ecriture->quantite);
        $this->assertEquals(2500, $ecriture->total);
    }

    /**
     * Test imputation annuelle avec indemnités mensuelles (UNITE_MOIS)
     * Doit calculer le nombre de mois réels d'activité
     *
     * @return void
     * @throws Exception
     */
    public function testImputationAnnuelAvecUniteMoisProportionnel()
    {
        $exercice_comptable = ExerciceComptable::find(3);

        $response = $this->json('POST', "/api/v2/imputation/annuel/{$exercice_comptable->id}");

        $response->assertStatus(200);

        // Vérifier les écritures pour les frais de bureau (type 2, UNITE_MOIS = 7)
        // Sapeur 1 : Commandant 150 CHF/mois × 12 mois
        $ecriture = Ecriture::where([
            ['sapeur_id', '=', $this->sapeurOneId],
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL],
            ['type_unite_id', '=', 7] // UNITE_MOIS
        ])->where('tarif', 150)->first();

        $this->assertNotNull($ecriture);
        $this->assertEquals(12, $ecriture->quantite);
        $this->assertEquals(1800, $ecriture->total); // 150 × 12

        // Sapeur 2 : Caissier 100 CHF/mois × 6 mois
        $ecriture = Ecriture::where([
            ['sapeur_id', '=', $this->sapeurTwoId],
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL],
            ['type_unite_id', '=', 7]
        ])->where('tarif', 100)->first();

        $this->assertNotNull($ecriture);
        $this->assertEquals(6, $ecriture->quantite);
        $this->assertEquals(600, $ecriture->total); // 100 × 6

        // Sapeur 3 : Fourrier 50 CHF/mois × 3 mois
        $ecriture = Ecriture::where([
            ['sapeur_id', '=', $this->sapeurThreeId],
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL],
            ['type_unite_id', '=', 7]
        ])->where('tarif', 50)->first();

        $this->assertNotNull($ecriture);
        $this->assertEquals(3, $ecriture->quantite);
        $this->assertEquals(150, $ecriture->total); // 50 × 3

        // Sapeur 4 : Vice-commandant 50 CHF/mois × 7 mois (mois partiels comptés comme complets)
        $ecriture = Ecriture::where([
            ['sapeur_id', '=', $this->sapeurFourId],
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL],
            ['type_unite_id', '=', 7]
        ])->where('tarif', 50)->first();

        $this->assertNotNull($ecriture);
        $this->assertEquals(7, $ecriture->quantite);
        $this->assertEquals(350, $ecriture->total); // 50 × 7
    }

    /**
     * Test que le nombre total d'écritures est correct
     *
     * @return void
     * @throws Exception
     */
    public function testImputationAnnuelNombreEcritures()
    {
        $exercice_comptable = ExerciceComptable::find(3);

        $response = $this->json('POST', "/api/v2/imputation/annuel/{$exercice_comptable->id}");

        $response->assertStatus(200);

        // Compter les écritures générées pour nos 4 sapeurs spécifiques
        $nbEcritures = Ecriture::where([
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL]
        ])->whereIn('sapeur_id', [
                    $this->sapeurOneId,
                    $this->sapeurTwoId,
                    $this->sapeurThreeId,
                    $this->sapeurFourId
                ])->count();

        // 4 sapeurs × 3 types d'indemnités (Indemnité annuelle + Frais de bureau + Frais de téléphone)
        // = 12 écritures attendues
        $this->assertEquals(12, $nbEcritures);
    }

    /**
     * Test que les frais de téléphone (type 3, MOIS) sont aussi proportionnels
     *
     * @return void
     * @throws Exception
     */
    public function testImputationAnnuelFraisTelephoneMensuel()
    {
        $exercice_comptable = ExerciceComptable::find(3);

        $response = $this->json('POST', "/api/v2/imputation/annuel/{$exercice_comptable->id}");

        $response->assertStatus(200);

        // Vérifier les frais de téléphone (type 3)
        // Sapeur 1 : Commandant 35 CHF/mois × 12 mois
        $ecriture = Ecriture::where([
            ['sapeur_id', '=', $this->sapeurOneId],
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL],
            ['type_unite_id', '=', 7]
        ])->where('tarif', 35)->first();

        $this->assertNotNull($ecriture);
        $this->assertEquals(12, $ecriture->quantite);
        $this->assertEquals(420, $ecriture->total); // 35 × 12

        // Sapeur 2 : Caissier 25 CHF/mois × 6 mois
        $ecriture = Ecriture::where([
            ['sapeur_id', '=', $this->sapeurTwoId],
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL],
            ['type_unite_id', '=', 7]
        ])->where('tarif', 25)->first();

        $this->assertNotNull($ecriture);
        $this->assertEquals(6, $ecriture->quantite);
        $this->assertEquals(150, $ecriture->total); // 25 × 6
    }

    /**
     * Test qu'une réimputation écrase les anciennes écritures
     *
     * @return void
     * @throws Exception
     */
    public function testReimputationAnnuel()
    {
        $exercice_comptable = ExerciceComptable::find(3);

        // Première imputation
        $this->json('POST', "/api/v2/imputation/annuel/{$exercice_comptable->id}")
            ->assertStatus(200);

        $nbEcritures1 = Ecriture::where([
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL]
        ])->count();

        // Deuxième imputation
        $this->json('POST', "/api/v2/imputation/annuel/{$exercice_comptable->id}")
            ->assertStatus(200);

        $nbEcritures2 = Ecriture::where([
            ['exercice_comptable_id', '=', $exercice_comptable->id],
            ['module', '=', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL]
        ])->count();

        // Le nombre d'écritures doit être identique (écrasement, pas duplication)
        $this->assertEquals($nbEcritures1, $nbEcritures2);
    }
}
