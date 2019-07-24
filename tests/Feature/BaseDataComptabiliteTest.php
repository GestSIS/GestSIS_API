<?php

namespace Tests\Unit;

use Exception;
use Tests\TestCase;

class BaseDataComptabiliteTest extends TestCase
{

    /**
     * Test index exercices-categorie
     *
     * @return void
     * @throws Exception
     */
    public function testFraisTypeIndexOK()
    {
        $response = $this->json('GET', "/api/v2/frais-types/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'annuels' => [
                        '*' => [
                            'id', 'designation', 'fonction_id', 'quantite', 'montant'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test index idemnite annuel type
     *
     * @return void
     * @throws Exception
     */
    public function testIndemniteTypeIndexOK()
    {
        $response = $this->json('GET', "/api/v2/indemnites-types/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'annuels' => [
                        '*' => [
                            'id', 'designation', 'fonction_id', 'quantite', 'montant'
                        ]
                    ],
                    'exercices' => [
                        '*' => [
                            'id', 'designation', 'compte_id', 'solde', 'indemnite'
                        ]
                    ],
                    'interventions' => [
                        '*' => [
                            'id', 'designation', 'compte_id', 'solde'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test index ecriture annuel
     *
     * @return void
     * @throws Exception
     */
    public function testEritureAnnuelIndexOK()
    {
        $exerciceComptable = 3;
        $response = $this->json('GET', "/api/v2/ecritures/annuel/$exerciceComptable");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'designation', 'total', 'quantite', 'sapeur_id'
                    ]
                ]
            ]);
    }

    /**
     * Test index ecritures exercice
     *
     * @return void
     * @throws Exception
     */
    public function testEcritureExerciceIndexOK()
    {
        $exerciceId = 1;
        $response = $this->json('GET', "/api/v2/ecritures/exercice/$exerciceId");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'designation', 'total', 'quantite', 'sapeur_id'
                    ]
                ]
            ]);
    }

    /**
     * Test index ecitures intervention
     *
     * @return void
     * @throws Exception
     */
    public function testEcritureInterventionIndexOK()
    {
        $interventionId = 393;
        $response = $this->json('GET', "/api/v2/ecritures/intervention/$interventionId");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'designation', 'total', 'quantite', 'sapeur_id'
                    ]
                ]
            ]);
    }

}
