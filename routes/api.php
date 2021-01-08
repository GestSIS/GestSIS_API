<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Spatie\HttpLogger\Middlewares\HttpLogger;

Route::group(['prefix' => 'v2', 'middleware' => HttpLogger::class], function () {

    // Etats de sortie 
    // TODO: Temporairement public, à déplacer
    Route::get('pdf-test/{id}', 'CompteController@generatePdf');
    Route::get('exercice/{id}/liste-presence', 'ExerciceController@listePresence');
    Route::get('exercice/{id}/liste-appel', 'ExerciceController@listeAppel');
    Route::get('exercice/{id}/liste-appel-localite', 'ExerciceController@listeAppelLocalite');

    Route::post('decompte/create', 'DecompteController@creer');
    Route::post('decompte/{id}/iso20022', 'DecompteController@iso20022');
    Route::get('decompte/', 'DecompteController@getAll');
    Route::get('decompte/{id}', 'DecompteController@get');
    Route::get('decompte/exercice-comptable/{id}', 'DecompteController@getByExerciceComptable');

    Route::post('paiement/{id}/iso20022', 'PaiementController@iso20022');
    Route::get('paiement/', 'PaiementController@getAll');
    Route::get('paiement/{id}', 'PaiementController@get');
    Route::get('paiement/decompte/{id}', 'PaiementController@getByDecompte');
    Route::get('paiement/exercice-comptable/{id}', 'PaiementController@getByExerciceComptable');

    Route::get('exercice-comptables/{ExerciceCompatbleId}/certificatSalaire/{SapeurId}', 'DecompteController@certificatSalaireSapeur');
    Route::get('exercice-comptables/{ExerciceCompatbleId}/certificatSalaire', 'DecompteController@certificatSalaire');


    Route::group(['middleware' => 'jwtToken'], function () {

        // Sapeurs
        Route::resource('sapeurs', 'SapeurController')->only(['index', 'show', 'store', 'update']); //, 'destroy']);//->middleware('role:effectif_read');

        Route::resource('sapeurs.groupes', 'SapeurGroupeController')->only(['index']);
        Route::resource('sapeurs.permis', 'SapeurPermisController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.telephones', 'SapeurTelephoneController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.fonctions', 'SapeurFonctionController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.grades', 'SapeurGradeController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.mutations', 'SapeurMutationController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.cours', 'SapeurCoursController')->only(['index', 'store', 'update', 'destroy']);
        Route::get('sapeurs/{id}/exercices/{exerciceComptableId}', 'SapeurExerciceController@index');
        Route::post('sapeurs/{id}/fin-fonctions', 'SapeurFonctionController@fin');
        Route::post('sapeurs/{id}/quitter-groupes', 'SapeurGroupeController@quitter');
        Route::post('sapeurs/{id}/supprimer-convocations', 'ConvocationsController@supprimerConvocations');

        // Exercices
        Route::resource('exercices', 'ExerciceController')->only(['index', 'show', 'store', 'update', 'destroy']); //->middleware('role:effectif_read');
        Route::post('exercices/{id}/valider', 'ExerciceController@valider')->name('api.v2.exercices.valider');

        Route::resource('exercices.sapeurs', 'ConvocationsController')->only(['index']);
        Route::post('exercices/{id}/sapeurs', 'ConvocationsController@store')->name('api.v2.exercices.sapeurs.store');
        Route::put('exercices/{id}/sapeurs', 'ConvocationsController@update')->name('api.v2.exercices.sapeurs.update');
        Route::delete('exercices/{id}/sapeurs', 'ConvocationsController@destroy')->name('api.v2.exercices.sapeurs.delete');

        // Exercices comptables
        Route::resource('exercice-comptables', 'ExerciceComptableController')->only(['index']);

        // Interventions
        Route::resource('interventions', 'InterventionController')->only(['index', 'show', 'store', 'update']);
        Route::post('interventions/{id}/valider', 'InterventionController@valider')->name('api.v2.interventions.valider');

        Route::resource('interventions.materiels', 'InterventionMaterielsController')->only(['index']);
        Route::post('interventions/{id}/materiels', 'InterventionMaterielsController@store')->name('api.v2.interventions.materiels.store');
        Route::put('interventions/{id}/materiels', 'InterventionMaterielsController@update')->name('api.v2.interventions.materiels.update');
        Route::delete('interventions/{id}/materiels', 'InterventionMaterielsController@destroy')->name('api.v2.interventions.materiels.delete');

        Route::resource('interventions.vehicules', 'InterventionVehiculesController')->only(['index']);
        Route::post('interventions/{id}/vehicules', 'InterventionVehiculesController@store')->name('api.v2.interventions.vehicules.store');
        Route::delete('interventions/{id}/vehicules', 'InterventionVehiculesController@destroy')->name('api.v2.interventions.vehicules.delete');

        Route::resource('interventions.missions', 'InterventionMissionsController')->only(['index']);
        Route::post('interventions/{id}/missions', 'InterventionMissionsController@store')->name('api.v2.interventions.missions.store');
        Route::put('interventions/{id}/missions', 'InterventionMissionsController@update')->name('api.v2.interventions.missions.update');
        Route::delete('interventions/{id}/missions', 'InterventionMissionsController@destroy')->name('api.v2.interventions.missions.delete');

        Route::resource('interventions.appels', 'InterventionAppelsController')->only(['index']);
        Route::post('interventions/{id}/appels', 'InterventionAppelsController@store')->name('api.v2.interventions.appels.store');
        Route::put('interventions/{id}/appels', 'InterventionAppelsController@update')->name('api.v2.interventions.appels.update');
        Route::delete('interventions/{id}/appels', 'InterventionAppelsController@destroy')->name('api.v2.interventions.appels.delete');

        Route::resource('interventions.sapeurs', 'InterventionSapeursController')->only(['index']);
        Route::post('interventions/{id}/sapeurs', 'InterventionSapeursController@store')->name('api.v2.interventions.sapeurs.store');
        Route::put('interventions/{id}/sapeurs', 'InterventionSapeursController@update')->name('api.v2.interventions.sapeurs.update');
        Route::delete('interventions/{id}/sapeurs', 'InterventionSapeursController@destroy')->name('api.v2.interventions.sapeurs.delete');

        Route::resource('interventions.quittances', 'InterventionQuittancesController')->only(['index']);
        Route::post('interventions/{id}/quittances', 'InterventionQuittancesController@store')->name('api.v2.interventions.quittances.store');
        Route::delete('interventions/{id}/quittances', 'InterventionQuittancesController@destroy')->name('api.v2.interventions.quittances.delete');

        Route::resource('interventions.groupes', 'InterventionGroupesController')->only(['index']);
        Route::post('interventions/{id}/groupes', 'InterventionGroupesController@store')->name('api.v2.interventions.groupes.store');
        Route::delete('interventions/{id}/groupes', 'InterventionGroupesController@destroy')->name('api.v2.interventions.groupes.delete');

        Route::resource('interventions.phases', 'InterventionPhasesController')->only(['index']);
        Route::post('interventions/{id}/phases', 'InterventionPhasesController@store')->name('api.v2.interventions.phases.store');
        Route::put('interventions/{id}/phases', 'InterventionPhasesController@update')->name('api.v2.interventions.phases.update');
        Route::delete('interventions/{id}/phases', 'InterventionPhasesController@destroy')->name('api.v2.interventions.phases.delete');

        // Vehicules
        Route::get('vehicules', 'VehiculeController@index')->name('api.v2.vehicule.index');

        // Materiel
        Route::get('materiels', 'MaterielController@index')->name('api.v2.materiel.index');

        // Téléphones
        Route::get('telephones', 'TelephoneController@index')->name('api.v2.telephone.index');

        // Groupes
        Route::get('groupes', 'GroupeController@index')->name('api.v2.groupes');
        Route::get('groupes-sapeurs', 'GroupeSapeursController@index')->name('api.v2.groupes-sapeurs');

        //Données de bases générales
        Route::get('localites', 'LocaliteController@index')->name('api.v2.localites');
        //TODO Communes

        // Données de bases Sapeur
        Route::get('permis', 'PermisController@index')->name('api.v2.permis');
        Route::get('civilites', 'CiviliteController@index')->name('api.v2.civilites');
        Route::get('grades', 'GradeController@index')->name('api.v2.grades');
        Route::get('fonctions', 'FonctionController@index')->name('api.v2.fonctions');
        Route::get('cours', 'CoursController@index')->name('api.v2.cours');
        Route::get('telephone-types', 'TelephoneTypeController@index')->name('api.v2.telephone-types');
        Route::get('mission-types', 'MissionTypeController@index')->name('api.v2.mission-types');

        // Données de bases exercices
        Route::get('exercice-categories', 'ExerciceCategorieController@index')->name('api.v2.exercice-categorie');
        Route::get('excuses-types', 'ExcuseTypeController@index')->name('api.v2.excuse-type');

        // Données de base intervention
        Route::resource('stat-federal', 'StatFederalController')->only(['index']);
        Route::resource('type-intervention', 'TypeInterventionController')->only(['index']);
        Route::resource('intervention-traitement', 'InterventionTraitementController')->only(['index']);
        Route::resource('phase-types', 'PhaseTypeController')->only(['index']);

        //Frais
        Route::post('imputation/intervention/{id}', 'ImputationController@intervention');
        Route::post('imputation/exercice/{id}', 'ImputationController@exercice');
        Route::post('imputation/annuel/{id}', 'ImputationController@annuel');
        Route::post('generer-amende/{id}/sapeur/{sapeurId}', 'AmendeController@sapeur');
        Route::post('generer-amende/{id}', 'AmendeController@annuel');

        Route::get('indemnites-types', 'IdemniteTypeController@index');
        Route::get('frais-types', 'FraisTypeController@index');

        Route::get('ecritures/annuel/{id}', 'EcritureController@annuel');
        Route::get('ecritures/amende/{id}', 'EcritureController@amende');
        Route::get('ecritures/intervention/{id}', 'EcritureController@intervention');
        Route::get('ecritures/exercice/{id}', 'EcritureController@exercice');
        Route::get('ecritures/{id}', 'EcritureController@all');

        Route::resource('comptes', 'CompteController')->only(['index']);
        Route::get('comptes/{id}/ecritures/{exerciceComptableId}', 'CompteController@ecritures');

        //Controles medicauxs
        Route::get('medecins/', 'MedecinController@all');
        Route::get('controles-medicaux-types', 'ControleMedicalTypeController@all');

        Route::resource('controles-medicaux', 'ControleMedicalController')->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::get('controles-medicaux/{id}/justificatif', 'JustificatifController@show');
        Route::post('controles-medicaux/{id}/justificatif', 'JustificatifController@store');
        Route::delete('controles-medicaux/{id}/justificatif', 'JustificatifController@destroy');
    });
});
