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

use Illuminate\Support\Facades\Route;
use Spatie\HttpLogger\Middlewares\HttpLogger;

Route::group(['prefix' => 'v2', 'middleware' => HttpLogger::class], function () {

    // Etats de sortie 
    // TODO: Temporairement public, à déplacer
    Route::get('pdf-test/{id}', 'CompteController@generatePdf');
    Route::get('exercice/{id}/liste-presence', 'ExerciceController@listePresence');
    Route::get('exercice/{id}/liste-appel', 'ExerciceController@listeAppel');
    Route::get('exercice/{id}/liste-appel-localite', 'ExerciceController@listeAppelLocalite');


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
        Route::get('exercice-comptables/{ExerciceComptableId}/certificat-salaire/{SapeurId}', 'DecompteController@certificatSalaireSapeur');
        Route::get('exercice-comptables/{ExerciceComptableId}/certificat-salaire', 'DecompteController@certificatSalaire');

        // Décomptes
        Route::post('decompte/create', 'DecompteController@creer');
        Route::post('decompte/{id}/iso20022', 'DecompteController@iso20022');
        Route::get('decompte/', 'DecompteController@getAll');
        Route::get('decompte/{id}', 'DecompteController@get');
        Route::get('decompte/exercice-comptable/{id}', 'DecompteController@getByExerciceComptable');

        // Paiements
        Route::post('paiement/{id}/iso20022', 'PaiementController@iso20022');
        Route::get('paiement/', 'PaiementController@getAll');
        Route::get('paiement/{id}', 'PaiementController@get');
        Route::get('paiement/decompte/{id}', 'PaiementController@getByDecompte');
        Route::get('paiement/exercice-comptable/{id}', 'PaiementController@getByExerciceComptable');

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

        // Static Params Sapeurs
        Route::get('civilites', 'CiviliteController@index')->name('api.v2.civilites');
        Route::get('localites', 'LocaliteController@index')->name('api.v2.localites');
        Route::get('telephone-types', 'TelephoneTypeController@index')->name('api.v2.telephone-types');
        Route::get('permis', 'PermisController@index')->name('api.v2.permis');

        // Static Params Intervention
        Route::resource('phase-types', 'PhaseTypeController')->only(['index']);
        Route::resource('stat-federal', 'StatFederalController')->only(['index', 'store', 'update']);
        
        // Données de bases Sapeur
        Route::get('groupes-sapeurs', 'GroupeSapeursController@index')->name('api.v2.groupes-sapeurs');
        Route::resource('groupes', 'GroupeController')->only(['index', 'store', 'update']);
        Route::resource('grades', 'GradeController')->only(['index', 'store', 'update']);
        Route::resource('fonctions', 'FonctionController')->only(['index', 'store', 'update']);
        Route::resource('cours', 'CoursController')->only(['index', 'store', 'update']);
        
        // Données de bases exercices
        Route::get('exercice-categories', 'ExerciceCategorieController@index')->name('api.v2.exercice-categorie');
        Route::get('excuses-types', 'ExcuseTypeController@index')->name('api.v2.excuse-type');
        
        // Données de base intervention
        Route::resource('vehicules', 'VehiculeController')->only(['index', 'store', 'update']);
        Route::resource('materiels', 'MaterielController')->only(['index', 'store', 'update']);
        Route::resource('type-intervention', 'TypeInterventionController')->only(['index', 'store', 'update']);
        Route::resource('stat-intervention', 'StatInterventionController')->only(['index', 'store', 'update']);
        Route::resource('intervention-traitement', 'InterventionTraitementController')->only(['index', 'store', 'update']);
        Route::resource('mission-types', 'MissionTypeController')->only(['index', 'store', 'update']);
        Route::resource('telephones', 'TelephoneController')->only(['index', 'store', 'update']);
        
        // Frais
        Route::post('imputation/intervention/{id}', 'ImputationController@intervention');
        Route::post('imputation/exercice/{id}', 'ImputationController@exercice');
        Route::post('imputation/annuel/{id}', 'ImputationController@annuel');
        Route::post('generer-amende/{id}/sapeur/{sapeurId}', 'AmendeController@sapeur');
        Route::post('generer-amende/{id}', 'AmendeController@annuel');

        Route::get('ecritures/annuel/{id}', 'EcritureController@annuel');
        Route::get('ecritures/amende/{id}', 'EcritureController@amende');
        Route::get('ecritures/intervention/{id}', 'EcritureController@intervention');
        Route::get('ecritures/exercice/{id}', 'EcritureController@exercice');
        Route::get('ecritures/{id}', 'EcritureController@all');
        
        Route::resource('comptes', 'CompteController')->only(['index']);
        Route::get('comptes/{id}/ecritures/{exerciceComptableId}', 'CompteController@ecritures');
        
        // Params Frais
        Route::get('indemnites-types', 'IndemniteTypeController@index');
        Route::resource('indemnites-exercice-types', 'IndemniteExerciceTypeController')->only(['index', 'store', 'update']);
        Route::resource('indemnites-intervention-types', 'IndemniteInterventionTypeController')->only(['index', 'store', 'update']);
        Route::resource('indemnites-annuel-types', 'IndemniteAnnuelTypeController')->only(['index', 'store', 'update']);
        Route::resource('frais-types', 'FraisTypeController')->only(['index', 'store', 'update']);
        
        // Controles médicaux
        Route::resource('controles-medicaux', 'ControleMedicalController')->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::get('controles-medicaux/{id}/justificatif', 'JustificatifController@show');
        Route::post('controles-medicaux/{id}/justificatif', 'JustificatifController@store');
        Route::delete('controles-medicaux/{id}/justificatif', 'JustificatifController@destroy');

        // Params Controles medicaux
        Route::resource('medecins', 'MedecinController')->only(['index', 'store', 'update']);
        Route::resource('controles-medicaux-types', 'ControleMedicalTypeController')->only(['index', 'store', 'update']);
    });
});
