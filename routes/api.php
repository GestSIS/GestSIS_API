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
use App\Application\Http\Middleware\DbSelector;

use App\Application\Http\Controllers\EmailController;
use App\Application\Http\Middleware\JwtTokenValidatorAuth;

// Route spécial pour le serveur d'authentification
Route::group(['prefix' => 'v2', 'middleware' => [HttpLogger::class, JwtTokenValidatorAuth::class]], function () {
    Route::get('email-validate', [EmailController::class, 'validateEmail']);
});

// Route::get('pdf-test/{id}', 'CompteController@generatePdf');

Route::group(['prefix' => 'v2', 'middleware' => [HttpLogger::class, DbSelector::class]], function () {

    // Paramètres accessible pour tout droit config
    Route::group(['middleware' => 'jwtTokenRole:utilisateur.config'], function () {
        Route::resource('sis-param', 'SisParamController')->only(['index']);
    });

    // Sis Params
    Route::group(['middleware' => 'jwtTokenRole:sis.config'], function () {
        Route::resource('sis-param', 'SisParamController')->only(['index', 'store']);
    });

    // Sapeurs
    Route::group(['middleware' => 'jwtTokenRole:sapeur.lecture'], function () {
        Route::get('effectif', 'SapeurController@effectif')->name('name');

        Route::resource('sapeurs', 'SapeurController')->only(['index', 'show']);
        Route::resource('sapeurs.permis', 'SapeurPermisController')->only(['index']);
        Route::resource('sapeurs.telephones', 'SapeurTelephoneController')->only(['index']);
        Route::resource('sapeurs.fonctions', 'SapeurFonctionController')->only(['index']);
        Route::resource('sapeurs.grades', 'SapeurGradeController')->only(['index']);
        Route::resource('sapeurs.mutations', 'SapeurMutationController')->only(['index']);
        Route::resource('sapeurs.cours', 'SapeurCoursController')->only(['index']);
        Route::resource('sapeurs.photo', 'SapeurPhotoController')->only(['index']);

        // Static Params Sapeurs ---
        Route::get('civilites', 'CiviliteController@index')->name('api.v2.civilites');
        Route::get('localites', 'LocaliteController@index')->name('api.v2.localites');
        Route::get('telephone-types', 'TelephoneTypeController@index')->name('api.v2.telephone-types');
        Route::get('permis', 'PermisController@index')->name('api.v2.permis');

        // Publipostage
        Route::post('publipostage', 'PublipostageController@index')->name('publipostage');

        // Params Sapeurs
        Route::resource('groupes', 'GroupeController')->only(['index']);
        Route::resource('grades', 'GradeController')->only(['index']);
        Route::resource('fonctions', 'FonctionController')->only(['index']);
        Route::resource('cours', 'CoursController')->only(['index']);
    });

    // Sapeurs
    Route::group(['middleware' => 'jwtTokenRole:sapeur.modification'], function () {
        Route::get('effectif', 'SapeurController@effectif')->name('name');

        Route::resource('sapeurs', 'SapeurController')->only(['index', 'show', 'store', 'update']); //, 'destroy']);//->middleware('role:effectif_read');
        Route::resource('sapeurs.groupes', 'SapeurGroupeController')->only(['index']);
        Route::resource('sapeurs.permis', 'SapeurPermisController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.telephones', 'SapeurTelephoneController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.fonctions', 'SapeurFonctionController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.grades', 'SapeurGradeController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.mutations', 'SapeurMutationController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.cours', 'SapeurCoursController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sapeurs.photo', 'SapeurPhotoController')->only(['index', 'store', 'delete']);

        Route::get('sapeurs/{id}/exercices/{exerciceComptableId}', 'SapeurExerciceController@index');
        Route::post('sapeurs/{id}/fin-fonctions', 'SapeurFonctionController@fin');
        Route::post('sapeurs/{id}/quitter-groupes', 'SapeurGroupeController@quitter');
        Route::post('sapeurs/{id}/supprimer-convocations', 'ConvocationsController@supprimerConvocations');
    });

    // Params Sapeur
    Route::group(['middleware' => 'jwtTokenRole:sapeur.config'], function () {
        Route::resource('sis-param', 'SisParamController')->only(['index']);
        Route::resource('grades', 'GradeController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('fonctions', 'FonctionController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('cours', 'CoursController')->only(['index', 'store', 'update', 'destroy']);
    });

    // Param organisation
    Route::group(['middleware' => 'jwtTokenRole:organisation.config'], function () {
        Route::resource('sis-param', 'SisParamController')->only(['index']);
        Route::resource('groupes', 'GroupeController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('groupes.sapeurs', 'GroupeSapeursController')->only(['store']);

        Route::get('rta-gestsis', 'ReferenceRtaController@getReferenceGestSis')->name('api.v2.rta.get-gestsis');
        Route::get('rta', 'ReferenceRtaController@getReferenceRta')->name('api.v2.rta.get-rta');
        Route::post('rta', 'ReferenceRtaController@setReference')->name('api.v2.rta.set');
        Route::delete('rta', 'ReferenceRtaController@resetReferenceRta')->name('api.v2.rta.reset-rta');
    });

    // Exercices
    Route::group(['middleware' => 'jwtTokenRole:exercice.presence'], function () {
        Route::resource('exercices', 'ExerciceController')->only(['index', 'show', 'store', 'update']);
        Route::get('exercices-derniers', 'ExerciceController@last')->name('api.v2.exercices.derniers');
        Route::resource('exercices.sapeurs', 'ConvocationsController')->only(['index']);
        Route::post('exercices/{id}/sapeurs', 'ConvocationsController@store')->name('api.v2.exercices.sapeurs.store');
        Route::put('exercices/{id}/sapeurs', 'ConvocationsController@update')->name('api.v2.exercices.sapeurs.update');
        Route::delete('exercices/{id}/sapeurs', 'ConvocationsController@destroy')->name('api.v2.exercices.sapeurs.delete');

        Route::get('exercices/{id}/liste-presence', 'ExerciceController@listePresence');
        Route::get('exercices/{id}/liste-appel', 'ExerciceController@listeAppel');
        // TODO: à implémenter
        // Route::get('exercices/{id}/liste-appel-localite', 'ExerciceController@listeAppelLocalite');

        // Convocations
        Route::post('convocation/{id}', 'ConvocationController@convoquer');

        // Statistiques
        Route::get('statistiques/{id}/presence', 'SapeurExerciceController@stat');

        // Route::resource('exercices.heures', 'HeureExerciceController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('heure-exercice-types', 'HeureExerciceTypeController')->only(['index']);
    });

    Route::group(['middleware' => 'jwtTokenRole:exercice.modification'], function () {
        Route::resource('exercices', 'ExerciceController')->only(['index', 'show', 'store', 'update', 'destroy']);
    });

    Route::group(['middleware' => 'jwtTokenRole:exercice.validation'], function () {
        Route::post('exercices/{id}/valider', 'ExerciceController@valider')->name('api.v2.exercices.valider');
    });

    // Params exercices
    Route::group(['middleware' => 'jwtTokenRole:exercice.config'], function () {
        Route::resource('sis-param', 'SisParamController')->only(['index']);
        Route::resource('exercice-categories', 'ExerciceCategorieController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('excuses-types', 'ExcuseTypeController')->only(['index', 'store', 'update', 'destroy']);
    });

    // Interventions
    Route::group(['middleware' => 'jwtTokenRole:intervention.modification'], function () {
        Route::resource('interventions', 'InterventionController')->only(['index', 'show', 'store', 'update', 'destroy']);

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

        // Impressions interventions
        Route::post('interventions/{id}/rapport', 'InterventionController@rapport')->name('api.v2.interventions.rapport');

        // Statistiques véhicule et matériel
        Route::get('statistiques/{id}/materiel', 'InterventionMaterielsController@stat');
        Route::get('statistiques/{id}/vehicule', 'InterventionVehiculesController@stat');
    });

    // Intervention validation
    Route::group(['middleware' => 'jwtTokenRole:intervention.validation'], function () {
        Route::post('interventions/{id}/valider', 'InterventionController@valider')->name('api.v2.interventions.valider');
    });

    // Static Params Intervention
    Route::group(['middleware' => 'jwtTokenRole:intervention.modification'], function () {
        // TODO: see to add the correct right for the following routes : 'store', 'update']);
        Route::resource('phase-types', 'PhaseTypeController')->only(['index']);
        Route::resource('stat-federal', 'StatFederalController')->only(['index']);
    });

    // Params intervention
    Route::group(['middleware' => 'jwtTokenRole:intervention.modification'], function () {
        Route::resource('vehicules', 'VehiculeController')->only(['index']);
        Route::resource('materiels', 'MaterielController')->only(['index']);
        Route::resource('type-intervention', 'TypeInterventionController')->only(['index']);
        Route::resource('stat-intervention', 'StatInterventionController')->only(['index']);
        Route::resource('intervention-traitement', 'InterventionTraitementController')->only(['index']);
        Route::resource('mission-types', 'MissionTypeController')->only(['index']);
        Route::resource('telephones', 'TelephoneController')->only(['index']);
    });

    Route::group(['middleware' => 'jwtTokenRole:intervention.config'], function () {
        Route::resource('sis-param', 'SisParamController')->only(['index']);
        Route::resource('type-intervention', 'TypeInterventionController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('vehicules', 'VehiculeController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('materiels', 'MaterielController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('stat-intervention', 'StatInterventionController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('intervention-traitement', 'InterventionTraitementController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('telephones', 'TelephoneController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('mission-types', 'MissionTypeController')->only(['index', 'store', 'update', 'destroy']);
    });

    // Exercices comptables
    Route::group(['middleware' => 'jwtTokenRole'], function () {
        Route::resource('exercices-comptable', 'ExerciceComptableController')->only(['index']);
    });

    // Comptabilite
    Route::group(['middleware' => 'jwtTokenRole:comptabilite.tout'], function () {
        Route::post('imputation/intervention/{id}', 'ImputationController@intervention');
        Route::delete('imputation/intervention/{id}', 'ImputationController@cancelIntervention');
        Route::post('imputation/exercice/{id}', 'ImputationController@exercice');
        Route::delete('imputation/exercice/{id}', 'ImputationController@cancelExercice');
        Route::post('imputation/annuel/{id}', 'ImputationController@annuel');
        Route::post('generer-amende/{id}/sapeur/{sapeurId}', 'AmendeController@sapeur');
        Route::post('generer-amende/{id}', 'AmendeController@annuel');

        Route::get('ecritures/exercices/{id}', 'EcritureController@exercices');
        Route::get('ecritures/exercice/{id}', 'EcritureController@exercice');
        Route::get('ecritures/annuel/{id}', 'EcritureController@annuel');
        Route::get('ecritures/amende/{id}', 'EcritureController@amende');
        Route::get('ecritures/intervention/{id}', 'EcritureController@intervention');
        Route::get('ecritures/{id}', 'EcritureController@all');

        Route::get('comptes/{id}/ecritures/{exerciceComptableId}', 'CompteController@ecritures');

        Route::get('exercices-comptable/{exercieComptableId}/comptes/{compteId}/justificatif', 'CompteController@justificatifIndividuel');
        Route::get('exercices-comptable/{exercieComptableId}/justificatif', 'CompteController@justificatifComplet');

        // Static param Comptabilite
        Route::get('unites', 'UniteController@index')->name('api.v2.unites');

        // Params Comptabilite
        Route::get('indemnites-types', 'IndemniteTypeController@index');
        Route::resource('comptes', 'CompteController')->only(['index']);
        Route::resource('frais-types', 'FraisTypeController')->only(['index']);
        Route::resource('indemnites-exercice-types', 'IndemniteExerciceTypeController')->only(['index']);
        Route::resource('indemnites-intervention-types', 'IndemniteInterventionTypeController')->only(['index']);
        Route::resource('indemnites-annuel-types', 'IndemniteAnnuelTypeController')->only(['index']);
        Route::resource('frais-annuel-types', 'FraisAnnuelTypeController')->only(['index']);
        Route::resource('ecriture-categories', 'EcritureCategorieController')->only(['index']);

        // Décomptes
        Route::post('decomptes/creer-annuel', 'DecompteController@creerAnnuel');
        Route::post('decomptes/creer-sapeur', 'DecompteController@creerSapeur');
        Route::post('decomptes/creer-exercice', 'DecompteController@creerExercice');
        Route::get('decomptes/exercice-comptable/{id}', 'DecompteController@getByExerciceComptable');
        Route::get('decomptes/{id}/iso20022', 'DecompteController@iso20022');
        Route::get('decomptes/{id}/print', 'DecompteController@print');
        Route::resource('decomptes', 'DecompteController')->only(['show', 'destroy']);

        // Paiements
        Route::get('paiements/exercice-comptable/{id}', 'PaiementController@getByExerciceComptable');
        Route::get('paiements/{id}/iso20022', 'PaiementController@iso20022');
        Route::get('paiements/{id}', 'PaiementController@get');

        // Amendes
        Route::post('generer-amendes/{id}/sapeur/{sapeurId}', 'AmendeController@sapeur');
        Route::post('generer-amendes/{id}', 'AmendeSapeurController@annuel');

        // Params Amendes
        Route::resource('amendes', 'AmendeController')->only(['index']);

        // Certificats de salaire
        Route::get('exercices-comptable/{ExerciceComptableId}/certificat-salaire', 'DecompteController@certificatSalaire');
        Route::get('exercices-comptable/{ExerciceComptableId}/certificat-salaire/{SapeurId}', 'DecompteController@certificatSalaireSapeur');
    });

    Route::group(['middleware' => 'jwtTokenRole:comptabilite.config'], function () {
        Route::resource('sis-param', 'SisParamController')->only(['index']);
        //TODO: Suppression d'exercices comptable ???
        Route::resource('exercices-comptable', 'ExerciceComptableController')->only(['index', 'store', 'update']);
        //TODO: Regarder que faire avec cloturer, déjà modifiable via la route update pour le moment
        // Route::post('exercice-comptable/{id}/cloturer', 'ExerciceComptableController@cloturer');

        Route::resource('sis-param', 'SisParamController')->only(['index']);
        Route::resource('avs-param', 'AvsParamController')->only(['index', 'store']);
        Route::resource('comptes', 'CompteController')->only(['index', 'store', 'update']);
        Route::resource('frais-types', 'FraisTypeController')->only(['index']);
        Route::resource('indemnites-exercice-types', 'IndemniteExerciceTypeController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('indemnites-intervention-types', 'IndemniteInterventionTypeController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('indemnites-annuel', 'IndemniteAnnuelController')->only(['store', 'update', 'destroy']);
        Route::resource('indemnites-annuel-types', 'IndemniteAnnuelTypeController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('frais-annuel', 'FraisAnnuelController')->only(['store', 'update', 'destroy']);
        Route::resource('frais-annuel-types', 'FraisAnnuelTypeController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('ecriture-categories', 'EcritureCategorieController')->only(['index', 'store', 'update']);

        Route::resource('amendes', 'AmendeController')->only(['index', 'store']);
        Route::resource('heure-exercice-types', 'HeureExerciceTypeController')->only(['index', 'store', 'update', 'destroy']);
    });

    // Controles médicaux
    Route::group(['middleware' => 'jwtTokenRole:controle_medical.tout'], function () {
        Route::resource('controles-medicaux', 'ControleMedicalController')->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::get('controles-medicaux/{id}/justificatif', 'JustificatifController@show');
        Route::post('controles-medicaux/{id}/justificatif', 'JustificatifController@store');
        Route::delete('controles-medicaux/{id}/justificatif', 'JustificatifController@destroy');

        // Params Controles médicaux
        Route::resource('medecins', 'MedecinController')->only(['index']);
        Route::resource('controles-medicaux-types', 'ControleMedicalTypeController')->only(['index']);
    });

    Route::group(['middleware' => 'jwtTokenRole:controle_medical.config'], function () {
        Route::resource('sis-param', 'SisParamController')->only(['index']);
        Route::resource('medecins', 'MedecinController')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('controles-medicaux-types', 'ControleMedicalTypeController')->only(['index', 'store', 'update', 'destroy']);
    });

    // TODO: Ajouter route type d'unité
    // (optionnel) Liste des dernières interventions (30 derniers jours)
});
