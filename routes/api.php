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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'v2'], function () {

    // Route::get('sis', 'SisApiController@index')->name('api.v2.sis.index');

    // // Authentication
    // Route::group(['prefix' => 'auth'], function(){
    //     Route::post('token', 'AuthApiController@getToken')->name('api.v2.auth.login');
    //     Route::post('token/refresh', 'AuthApiController@refreshToken')->name('api.v2.auth.refresh_token')->middleware('jwt.auth');
    // });

    // Route::post('user/signup', 'UserApiController@signup')->name('api.v2.user.signup');

    // // TODO: Authorization required
    // Route::group(['middleware' => 'jwt.auth'], function(){

        // Sapeurs
        Route::resource('sapeurs', 'SapeurController')->only(['index', 'show', 'store', 'update']);//, 'destroy']);//->middleware('role:effectif_read');

        Route::resource('sapeurs.groupes', 'SapeurGroupeController')->only(['index']);
        Route::resource('sapeurs.permis', 'SapeurPermisController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.telephones', 'SapeurTelephoneController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.fonctions', 'SapeurFonctionController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.grades', 'SapeurGradeController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.mutations', 'SapeurMutationController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.cours', 'SapeurCoursController')->only(['index', 'store', 'update', 'destroy']);

        // Exercices
        Route::resource('exercices', 'ExerciceController')->only(['index', 'show', 'store', 'update', 'destroy']);//->middleware('role:effectif_read');
        Route::post('exercices/{id}/valider', 'ExerciceController@valider')->name('api.v2.exercices.valider');

        Route::resource('exercices.sapeurs', 'ExerciceSapeursController')->only(['index']);
        Route::post('exercices/{id}/sapeurs', 'ExerciceSapeursController@store')->name('api.v2.exercices.sapeurs.store');
        Route::put('exercices/{id}/sapeurs', 'ExerciceSapeursController@update')->name('api.v2.exercices.sapeurs.update');
        Route::delete('exercices/{id}/sapeurs', 'ExerciceSapeursController@destroy')->name('api.v2.exercices.sapeurs.delete');

        // Exercices comptables
        Route::resource('exercice-comptables', 'ExerciceComptableController')->only(['index']);
//        Route::resource('exercice-comptables.exercices', 'ExerciceComptableController')->only(['index']);
//        Route::resource('exercice-comptables.interventions', 'ExerciceComptableController')->only(['index']);

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

        // Controles médicaux
        // Route::get('cmedtype', 'BaseDataApiController@cmedtype')->name('api.v2.basedata.cmedtype');

        //Frais
        Route::resource('frais-annuel-types', 'PhaseTypeController')->only(['index']);
        Route::resource('indemnite-annuel-types', 'PhaseTypeController')->only(['index']);
        Route::resource('indemnite-exercice-types', 'PhaseTypeController')->only(['index']);
        Route::resource('indemnite-intervention-types', 'PhaseTypeController')->only(['index']);

        Route::post('imputation/intervention/{id}', 'ImputationController@intervention');
        Route::post('imputation/exercice/{id}', 'ImputationController@exercice');
        Route::post('imputation/annuel/{id}', 'ImputationController@annuel');

        Route::get('indemnites-types', 'IdemniteTypeController@index');
        Route::get('frais-types', 'FraisTypeController@index');

        Route::get('ecritures/annuel/{id}', 'EcritureController@annuel');
        Route::get('ecritures/intervention/{id}', 'EcritureController@intervention');
        Route::get('ecritures/exercice/{id}', 'EcritureController@exercice');

        Route::resource('comptes', 'CompteController')->only(['index']);
        Route::get('comptes/{id}/ecritures/{exerciceComptableId}', 'CompteController@ecritures');

        Route::get('pdf-test', 'CompteController@generatePdf');
        //SUPPRIMER

        // Sis
        // Route::get('localities', 'SisApiController@localities')->name('api.v2.sis.localities');

        // Route::get('excusestypes', 'ExerciceApiController@excusesTypes')->name('api.v2.exercice.excusesTypes')->middleware('role:exercice_read');
        // Renommé en excuse-types

        // // Transfert
        // Route::get('transfert', 'TransfertApiController@index')->name('api.v2.transfert.index')->middleware('role:transfert_all');
        // Route::post('transfert/{slug}', 'TransfertApiController@create')->name('api.v2.transfert.create')->middleware('role:transfert_all');
        // Route::put('transfert/{id}/recu', 'TransfertApiController@received')->name('api.v2.transfert.received')->middleware('role:transfert_all');

    // });
});

//TODO: Implement those route for retro compatibility
// Route::group(['prefix' => 'v1'], function(){

//     Route::get('sis', 'SisApiController@index')->name('api.v1.sis.index');

//     // Authentication
//     Route::group(['prefix' => 'auth'], function(){
//         Route::post('token', 'AuthApiController@getToken')->name('api.v1.auth.login');
//         Route::post('token/refresh', 'AuthApiController@refreshToken')->name('api.v1.auth.refresh_token')->middleware('jwt.auth');
//     });

//     Route::post('user/signup', 'UserApiController@signup')->name('api.v1.user.signup');

//     // Authorization required
//     Route::group(['middleware' => 'jwt.auth'], function(){
//         // Sapeurs
//         Route::get('sapeurs', 'SapeurApiController@index')->name('api.v1.sapeur.index')->middleware('role:effectif_read');
//         Route::get('sapeurs/{id}', 'SapeurApiController@show')->name('api.v1.sapeur.show')->middleware('role:effectif_read');

//         // Exercices
//         Route::get('excusestypes', 'ExerciceApiController@excusesTypes')->name('api.v1.exercice.excusesTypes')->middleware('role:exercice_read');
//         Route::get('exercices', 'ExerciceApiController@index')->name('api.v1.exercice.index')->middleware('role:exercice_read');
//         Route::get('exercices/{id}', 'ExerciceApiController@show')->name('api.v1.exercice.show')->middleware('role:exercice_read');

//         // Interventions
//         Route::get('interventions', 'InterventionApiController@index')->name('api.v1.intervention.index')->middleware('role:intervention_read');
//         Route::get('interventions/{id}', 'InterventionApiController@show')->name('api.v1.intervention.show')->middleware('role:intervention_read');

//         // Vehicules
//         Route::get('vehicule', 'VehiculeApiController@index')->name('api.v1.vehicule.index');

//         // Materiel
//         Route::get('materiel', 'MaterielApiController@index')->name('api.v1.materiel.index');

//         // Sis
//         Route::get('localities', 'SisApiController@localities')->name('api.v1.sis.localities');

//         // Téléphones
//         Route::get('telephones', 'TelephoneApiController@index')->name('api.v1.telephone.index');

//         // Groupes
//         Route::get('groupes', 'GroupeApiController@index')->name('api.v1.groupe.index');

//         // Transfert
//         Route::get('transfert', 'TransfertApiController@index')->name('api.v1.transfert.index')->middleware('role:transfert_all');
//         Route::post('transfert/{slug}', 'TransfertApiController@create')->name('api.v1.transfert.create')->middleware('role:transfert_all');
//         Route::put('transfert/{id}/recu', 'TransfertApiController@received')->name('api.v1.transfert.received')->middleware('role:transfert_all');

//         // Données de bases
//         Route::get('statfederal', 'BaseDataApiController@statfederal')->name('api.v1.basedata.statfederal');
//         Route::get('typeintervention', 'BaseDataApiController@typeintervention')->name('api.v1.basedata.typeintervention');
//         Route::get('grade', 'BaseDataApiController@grade')->name('api.v1.basedata.grade');
//         Route::get('cours', 'BaseDataApiController@cours')->name('api.v1.basedata.cours');
//         Route::get('districtlocality', 'BaseDataApiController@districtlocality')->name('api.v1.basedata.districtlocality');
//         Route::get('cmedtype', 'BaseDataApiController@cmedtype')->name('api.v1.basedata.cmedtype');
//     });
// });
