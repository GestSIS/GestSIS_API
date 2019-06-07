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
        //TODO: Implement those route for retro compatibility

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
        Route::resource('exercices', 'ExerciceController')->only(['index', 'show', 'store', 'update']);//, 'destroy']);//->middleware('role:effectif_read');

        Route::resource('exercices.sapeurs', 'ExerciceSapeurController')->only(['index']);
        Route::post('exercices/{id}/sapeurs', 'ExerciceSapeurController@store')->name('api.v2.exercices.sapeurs.store');
        Route::put('exercices/{id}/sapeurs', 'ExerciceSapeurController@update')->name('api.v2.exercices.sapeurs.update');
        Route::delete('exercices/{id}/sapeurs', 'ExerciceSapeurController@destroy')->name('api.v2.exercices.sapeurs.delete');

        // Exercices comptables
        Route::resource('exercice-comptables', 'ExerciceComptableController')->only(['index']);
//        Route::resource('exercice-comptables.exercices', 'ExerciceComptableController')->only(['index']);
//        Route::resource('exercice-comptables.interventions', 'ExerciceComptableController')->only(['index']);

        // Interventions
        Route::resource('interventions', 'InterventionController')->only(['index', 'show']);

        Route::resource('exercices.materiels', 'ExerciceMaterielsController')->only(['index']);
        Route::post('exercices/{id}/materiels', 'ExerciceMaterielsController@store')->name('api.v2.exercices.materiels.store');
        Route::put('exercices/{id}/materiels', 'ExerciceMaterielsController@update')->name('api.v2.exercices.materiels.update');
        Route::delete('exercices/{id}/materiels', 'ExerciceMaterielsController@destroy')->name('api.v2.exercices.materiels.delete');

        Route::resource('exercices.vehicules', 'ExerciceVehiculesController')->only(['index']);
        Route::post('exercices/{id}/vehicules', 'ExerciceVehiculesController@store')->name('api.v2.exercices.vehicules.store');
        Route::put('exercices/{id}/vehicules', 'ExerciceVehiculesController@update')->name('api.v2.exercices.vehicules.update');
        Route::delete('exercices/{id}/vehicules', 'ExerciceVehiculesController@destroy')->name('api.v2.exercices.vehicules.delete');

        Route::resource('exercices.missions', 'ExerciceMissionsController')->only(['index']);
        Route::post('exercices/{id}/missions', 'ExerciceMissionsController@store')->name('api.v2.exercices.missions.store');
        Route::put('exercices/{id}/missions', 'ExerciceMissionsController@update')->name('api.v2.exercices.missions.update');
        Route::delete('exercices/{id}/missions', 'ExerciceMissionsController@destroy')->name('api.v2.exercices.missions.delete');

        Route::resource('exercices.appels', 'ExerciceAppelsController')->only(['index']);
        Route::post('exercices/{id}/appels', 'ExerciceAppelsController@store')->name('api.v2.exercices.appels.store');
        Route::put('exercices/{id}/appels', 'ExerciceAppelsController@update')->name('api.v2.exercices.appels.update');
        Route::delete('exercices/{id}/appels', 'ExerciceAppelsController@destroy')->name('api.v2.exercices.appels.delete');

        Route::resource('exercices.sapeurs', 'ExerciceSapeursController')->only(['index']);
        Route::post('exercices/{id}/sapeurs', 'ExerciceSapeursController@store')->name('api.v2.exercices.sapeurs.store');
        Route::put('exercices/{id}/sapeurs', 'ExerciceSapeursController@update')->name('api.v2.exercices.sapeurs.update');
        Route::delete('exercices/{id}/sapeurs', 'ExerciceSapeursController@destroy')->name('api.v2.exercices.sapeurs.delete');

        Route::resource('exercices.quittances', 'ExerciceQuittancesController')->only(['index']);
        Route::post('exercices/{id}/quittances', 'ExerciceQuittancesController@store')->name('api.v2.exercices.quittances.store');
        Route::delete('exercices/{id}/quittances', 'ExerciceQuittancesController@destroy')->name('api.v2.exercices.quittances.delete');

        Route::resource('exercices.groupes', 'ExerciceGroupesController')->only(['index']);
        Route::post('exercices/{id}/groupes', 'ExerciceGroupesController@store')->name('api.v2.exercices.groupes.store');
        Route::delete('exercices/{id}/groupes', 'ExerciceGroupesController@destroy')->name('api.v2.exercices.groupes.delete');

        // Vehicules
        Route::get('vehicules', 'VehiculeController@index')->name('api.v2.vehicule.index');

        // Materiel
        Route::get('materiels', 'MaterielController@index')->name('api.v2.materiel.index');

        // Téléphones
        Route::get('telephones', 'TelephoneController@index')->name('api.v2.telephone.index');

        // Groupes
        Route::get('groupes', 'GroupeController@index')->name('api.v2.groupes');

        //Données de bases générales
        Route::get('localites', 'LocaliteController@index')->name('api.v2.localites');
        //TODO Communes

        // Données de bases Sapeur
        Route::get('permis', 'PermisController@index')->name('api.v2.permis');
        Route::get('civilites', 'CiviliteController@index')->name('api.v2.civilites');
        Route::get('grades', 'GradeController@index')->name('api.v2.grades');
        Route::get('fonctions', 'FonctionController@index')->name('api.v2.fonctions');
        Route::get('cours', 'CoursController@index')->name('api.v2.cours');
        Route::get('telephone-types', 'TelephoneTypeController@index')->name('api.v2.telephones-type');

        // Données de bases exercices
        Route::get('exercice-categories', 'ExerciceCategorieController@index')->name('api.v2.exercice-categorie');
        Route::get('excuses-types', 'ExcuseTypeController@index')->name('api.v2.excuse-type');

        // Données de base intervention
        Route::resource('stat-federal', 'StatFederalController')->only(['index']);
        Route::resource('type-intervention', 'TypeInterventionController')->only(['index']);
        Route::resource('intervention-traitement', 'InterventionTraitementController')->only(['index']);

        // Controles médicaux
        // Route::get('cmedtype', 'BaseDataApiController@cmedtype')->name('api.v2.basedata.cmedtype');

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
