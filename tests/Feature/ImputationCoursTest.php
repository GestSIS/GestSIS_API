<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Cours;
use App\Infrastructure\Models\CoursSapeur;
use App\Infrastructure\Models\IndemniteCoursType;
use App\Infrastructure\Models\IndemniteCoursFonction;
use App\Infrastructure\Models\Localite;
use App\Infrastructure\Models\Sapeur;
use Exception;
use Tests\TestCase;
use App\Domaine\API\SapeurService;
use App\Domaine\API\ImputationService;
use App\Domaine\API\CoursService;

class ImputationCoursTest extends TestCase
{

    protected $imputationService;
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

        $sapeurService = $this->app->make(SapeurService::class);
        $this->imputationService = $this->app->make(ImputationService::class);

        // Création de 3 sapeurs
        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurOneId = $sapeurService->createSapeur($data)->id;

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurTwoId = $sapeurService->createSapeur($data)->id;

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurThreeId = $sapeurService->createSapeur($data)->id;

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

        $ecritures = $this->imputationService->imputationCours($this->coursSapeurOneId, $param);

        // Vérifier qu'au moins une écriture a été créée
        $this->assertTrue(count($ecritures) >= 1);
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

        $ecritures = $this->imputationService->imputationCours($this->coursSapeurTwoId, $param);

        // Vérifier qu'au moins une écriture a été créée
        $this->assertTrue(count($ecritures) >= 1);
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

        $ecritures = $this->imputationService->imputationCours($this->coursSapeurThreeId, $param);

        // Vérifier qu'au moins une écriture a été créée
        $this->assertTrue(count($ecritures) >= 1);
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

        $ecritures = $this->imputationService->imputationCours($this->coursSapeurOneId, $param);
        $this->assertTrue(count($ecritures) >= 1);

        // Puis annuler l'imputation
        $result = $this->imputationService->annulerImputationCours($this->coursSapeurOneId);

        // Vérifier que l'annulation a réussi
        $this->assertTrue($result === true || is_string($result));
    }
}
