<?php

namespace Tests\Feature;

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
    public function testFraisIndemnitesTypeIndexOk()
    {
        $response = $this->json('GET', "/api/v2/frais-indemnites-types/");
        // dd($response);
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'annuels' => [
                        '*' => [
                            'id', 'designation', 'cumulable', 'compte_id', 'ecriture_categorie_id', 'type',
                            'frais_indemnite_annuels' => [
                                '*' => [
                                    'id', 'fonction_id', 'quantite', 'montant', 'frais_indemnite_annuel_type_id', 'type_unite_id'
                                ]
                            ],
                        ]
                    ],
                    'exercices' => [
                        '*' => [
                            'id', 'designation', 'type_unite_id', 'ecriture_categorie_id', 'par_fonction',
                            'fonctions' => [
                                '*' => [
                                    'tarif',
                                    'fonction_id',
                                    'indemnite_exe_id',
                                    'compte_id',
                                    'type',
                                ]
                            ]
                        ]
                    ],
                    'interventions' => [
                        '*' => [
                            'id',
                            'designation',
                            'compte_id',
                            'tarif',
                            'tarif_pro_rata',
                            'tarif_min',
                            'tarif_min_pour',
                            'tarif_min_pro_rata',
                            'taux_weekend',
                            'taux_nuit',
                            'debut',
                            'fin',
                            'phase_id',
                            'type_unite_id',
                            'ecriture_categorie_id',
                            'par_fonction',
                            'type',
                            'fonctions' => [
                                '*' => [] //TODO: See what to write with par fonction
                            ]
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
    public function testEritureAnnuelIndexOk()
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
    public function testEcritureExerciceIndexOk()
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
    public function testEcritureInterventionIndexOk()
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
