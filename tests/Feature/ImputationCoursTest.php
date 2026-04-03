<?php

namespace Tests\Feature;

use App\Models\Cours;
use App\Models\CoursSapeur;
use App\Models\IndemniteCoursType;
use App\Models\IndemniteCoursFonction;
use App\Models\Localite;
use App\Models\Sapeur;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImputationCoursTest extends TestCase
{
    use DatabaseTransactions;

    protected $sapeurOneId;
    protected $sapeurTwoId;
    protected $sapeurThreeId;
    protected $coursSapeurOneId;
    protected $coursSapeurTwoId;
    protected $coursSapeurThreeId;
    protected $exerciceComptableId;
    protected $indemniteTypeJourId;
    protected $indemniteTypePieceId;
    protected $indemniteTypeForfaitId;

    protected function setUp(): void
    {
        parent::setUp();

        // Création de 3 sapeurs
        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['type'] = 0;
        $this->sapeurOneId = $this->json('POST', '/api/v2/sapeurs', $data)->json('data.id');

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['type'] = 0;
        $this->sapeurTwoId = $this->json('POST', '/api/v2/sapeurs', $data)->json('data.id');

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['type'] = 0;
        $this->sapeurThreeId = $this->json('POST', '/api/v2/sapeurs', $data)->json('data.id');

        // Création d'un cours de référence
        $cours = Cours::create([
            'designation' => 'Formation Test',
            'abreviation' => 'FT',
            'tri' => 1,
            'duree' => 2.0
        ]);

        // Récupération d'une localité existante ou création
        $localite = Localite::first();
        if ($localite === null) {
            $localite = Localite::create([
                'designation' => 'Test Localite',
                'tri' => 1
            ]);
        }

        // Utilisation de l'exercice comptable 2 (2018) qui est ouvert (boucle=0)
        $this->exerciceComptableId = 2;

        // Création de 3 cours_sapeur pour chaque sapeur
        $coursSapeurOne = CoursSapeur::create([
            'cours_id' => $cours->id,
            'sapeur_id' => $this->sapeurOneId,
            'localite_id' => $localite->id,
            'date' => '2018-01-15',
            'duree' => 2.0
        ]);
        $this->coursSapeurOneId = $coursSapeurOne->id;

        $coursSapeurTwo = CoursSapeur::create([
            'cours_id' => $cours->id,
            'sapeur_id' => $this->sapeurTwoId,
            'localite_id' => $localite->id,
            'date' => '2018-01-16',
            'duree' => 1.5
        ]);
        $this->coursSapeurTwoId = $coursSapeurTwo->id;

        $coursSapeurThree = CoursSapeur::create([
            'cours_id' => $cours->id,
            'sapeur_id' => $this->sapeurThreeId,
            'localite_id' => $localite->id,
            'date' => '2018-01-17',
            'duree' => 3.0
        ]);
        $this->coursSapeurThreeId = $coursSapeurThree->id;

        // Création des types d'indemnité de cours pour les tests
        // Type 1: Par jour (UNITE_JOUR = 5)
        $indemniteTypeJour = IndemniteCoursType::create([
            'designation' => 'Formation par jour',
            'ecriture_categorie_id' => 5 // Formation
        ]);
        $this->indemniteTypeJourId = $indemniteTypeJour->id;
        IndemniteCoursFonction::create([
            'indemnite_cours_id' => $indemniteTypeJour->id,
            'type' => 10, // Type d'écriture
            'tarif' => 50.00,
            'type_unite_id' => 5, // jour
            'compte_id' => 7, // Compte formation
            'fonction_id' => null
        ]);

        // Type 2: Par pièce (UNITE_PIECE = 1)
        $indemniteTypePiece = IndemniteCoursType::create([
            'designation' => 'Formation par pièce',
            'ecriture_categorie_id' => 5
        ]);
        $this->indemniteTypePieceId = $indemniteTypePiece->id;
        IndemniteCoursFonction::create([
            'indemnite_cours_id' => $indemniteTypePiece->id,
            'type' => 10,
            'tarif' => 100.00,
            'type_unite_id' => 1, // pièce
            'compte_id' => 7,
            'fonction_id' => null
        ]);

        // Type 3: Par forfait (UNITE_FORFAIT = 6)
        $indemniteTypeForfait = IndemniteCoursType::create([
            'designation' => 'Formation par forfait',
            'ecriture_categorie_id' => 5
        ]);
        $this->indemniteTypeForfaitId = $indemniteTypeForfait->id;
        IndemniteCoursFonction::create([
            'indemnite_cours_id' => $indemniteTypeForfait->id,
            'type' => 10,
            'tarif' => 150.00,
            'type_unite_id' => 6, // forfait
            'compte_id' => 7,
            'fonction_id' => null
        ]);
    }

    /**
     * Test imputation cours par jour
     *
     * @return void
     * @throws Exception
     */
    public function testImputationCoursParJour()
    {
        $param = [
            "exercice_comptable_id" => $this->exerciceComptableId,
            "indemnite_cours_type_id" => $this->indemniteTypeJourId
        ];

        $response = $this->json('POST', "/api/v2/imputation/cours/{$this->coursSapeurOneId}", $param);
        $response->assertStatus(200);
        $this->assertTrue(count($response->json('data')) >= 1);
    }

    /**
     * Test imputation cours par pièce
     *
     * @return void
     * @throws Exception
     */
    public function testImputationCoursParPiece()
    {
        $param = [
            "exercice_comptable_id" => $this->exerciceComptableId,
            "indemnite_cours_type_id" => $this->indemniteTypePieceId
        ];

        $response = $this->json('POST', "/api/v2/imputation/cours/{$this->coursSapeurTwoId}", $param);
        $response->assertStatus(200);
        $this->assertTrue(count($response->json('data')) >= 1);
    }

    /**
     * Test imputation cours par forfait
     *
     * @return void
     * @throws Exception
     */
    public function testImputationCoursParForfait()
    {
        $param = [
            "exercice_comptable_id" => $this->exerciceComptableId,
            "indemnite_cours_type_id" => $this->indemniteTypeForfaitId
        ];

        $response = $this->json('POST', "/api/v2/imputation/cours/{$this->coursSapeurThreeId}", $param);
        $response->assertStatus(200);
        $this->assertTrue(count($response->json('data')) >= 1);
    }

    /**
     * Test annulation imputation cours
     *
     * @return void
     * @throws Exception
     */
    public function testAnnulerImputationCours()
    {
        // D'abord imputer un cours
        $param = [
            "exercice_comptable_id" => $this->exerciceComptableId,
            "indemnite_cours_type_id" => $this->indemniteTypeJourId
        ];

        $response = $this->json('POST', "/api/v2/imputation/cours/{$this->coursSapeurOneId}", $param);
        $this->assertTrue(count($response->json('data')) >= 1);

        // Puis annuler l'imputation
        $result = $this->json('DELETE', "/api/v2/imputation/cours/{$this->coursSapeurOneId}");

        // Vérifier que l'annulation a réussi
        $result->assertStatus(200);
    }
}
