<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use Illuminate\Support\Facades\Route;
use Spatie\HttpLogger\Middlewares\HttpLogger;
use App\Application\Http\Middleware\DbSelector;
use App\Application\Http\Middleware\JwtTokenValidatorAuth;

use App\Application\Http\Controllers\AlarmeController;
use App\Application\Http\Controllers\AmendeController;
use App\Application\Http\Controllers\AmendeSapeurController;
use App\Application\Http\Controllers\AspsmsController;
use App\Application\Http\Controllers\AspsmsParamController;
use App\Application\Http\Controllers\AvsParamController;
use App\Application\Http\Controllers\CiviliteController;
use App\Application\Http\Controllers\ComptabiliteStatistiqueController;
use App\Application\Http\Controllers\CompteController;
use App\Application\Http\Controllers\ControleMedicalController;
use App\Application\Http\Controllers\ControleMedicalTypeController;
use App\Application\Http\Controllers\ConvocationController;
use App\Application\Http\Controllers\ConvocationsController;
use App\Application\Http\Controllers\CoursController;
use App\Application\Http\Controllers\CoursSapeurController;
use App\Application\Http\Controllers\DecompteController;
use App\Application\Http\Controllers\EcritureCategorieController;
use App\Application\Http\Controllers\EcritureController;
use App\Application\Http\Controllers\EmailController;
use App\Application\Http\Controllers\ExcuseController;
use App\Application\Http\Controllers\ExcuseParamController;
use App\Application\Http\Controllers\ExcuseTypeController;
use App\Application\Http\Controllers\ExerciceCategorieController;
use App\Application\Http\Controllers\ExerciceComptableController;
use App\Application\Http\Controllers\ExerciceController;
use App\Application\Http\Controllers\FonctionController;
use App\Application\Http\Controllers\FraisIndemniteAnnuelTypeController;
use App\Application\Http\Controllers\FraisIndemniteTypeController;
use App\Application\Http\Controllers\GradeController;
use App\Application\Http\Controllers\GroupeController;
use App\Application\Http\Controllers\GroupeSapeursController;
use App\Application\Http\Controllers\HeureExerciceTypeController;
use App\Application\Http\Controllers\ImputationController;
use App\Application\Http\Controllers\IndemniteCoursTypeController;
use App\Application\Http\Controllers\IndemniteExerciceTypeController;
use App\Application\Http\Controllers\IndemniteInterventionTypeController;
use App\Application\Http\Controllers\InterventionAppelsController;
use App\Application\Http\Controllers\InterventionController;
use App\Application\Http\Controllers\InterventionGroupesController;
use App\Application\Http\Controllers\InterventionMaterielsController;
use App\Application\Http\Controllers\InterventionMissionsController;
use App\Application\Http\Controllers\InterventionPhasesController;
use App\Application\Http\Controllers\InterventionQuittancesController;
use App\Application\Http\Controllers\InterventionSapeursController;
use App\Application\Http\Controllers\InterventionStatistiqueController;
use App\Application\Http\Controllers\InterventionTraitementController;
use App\Application\Http\Controllers\InterventionVehiculesController;
use App\Application\Http\Controllers\JustificatifController;
use App\Application\Http\Controllers\LocaliteController;
use App\Application\Http\Controllers\LocaliteSisController;
use App\Application\Http\Controllers\MaterielController;
use App\Application\Http\Controllers\MatPersoAlerteController;
use App\Application\Http\Controllers\MatPersoAlerteTypeController;
use App\Application\Http\Controllers\MatPersoAttributionController;
use App\Application\Http\Controllers\MatPersoCategorieController;
use App\Application\Http\Controllers\MatPersoController;
use App\Application\Http\Controllers\MatPersoEventController;
use App\Application\Http\Controllers\MatPersoEventTypeController;
use App\Application\Http\Controllers\MatPersoTypeController;
use App\Application\Http\Controllers\MedecinController;
use App\Application\Http\Controllers\MesDecomptesController;
use App\Application\Http\Controllers\MesExcusesController;
use App\Application\Http\Controllers\MesExercicesController;
use App\Application\Http\Controllers\MesInfosController;
use App\Application\Http\Controllers\MesInterventionsController;
use App\Application\Http\Controllers\MesTravauxController;
use App\Application\Http\Controllers\MissionTypeController;
use App\Application\Http\Controllers\MonMaterielController;
use App\Application\Http\Controllers\PaiementController;
use App\Application\Http\Controllers\PermisController;
use App\Application\Http\Controllers\PhaseTypeController;
use App\Application\Http\Controllers\PublipostageController;
use App\Application\Http\Controllers\ReferenceRtaController;
use App\Application\Http\Controllers\SapeurControleMedicalController;
use App\Application\Http\Controllers\SapeurController;
use App\Application\Http\Controllers\SapeurCoursController;
use App\Application\Http\Controllers\SapeurExerciceController;
use App\Application\Http\Controllers\SapeurFonctionController;
use App\Application\Http\Controllers\SapeurGradeController;
use App\Application\Http\Controllers\SapeurGroupeController;
use App\Application\Http\Controllers\SapeurMaterielController;
use App\Application\Http\Controllers\SapeurMutationController;
use App\Application\Http\Controllers\SapeurPermisController;
use App\Application\Http\Controllers\SapeurPhotoController;
use App\Application\Http\Controllers\SapeurStatistiqueController;
use App\Application\Http\Controllers\SapeurTelephoneController;
use App\Application\Http\Controllers\SisLogoController;
use App\Application\Http\Controllers\SisParamController;
use App\Application\Http\Controllers\StatFederalController;
use App\Application\Http\Controllers\StatInterventionController;
use App\Application\Http\Controllers\TelephoneController;
use App\Application\Http\Controllers\TelephoneTypeController;
use App\Application\Http\Controllers\TravailController;
use App\Application\Http\Controllers\TravailTypeController;
use App\Application\Http\Controllers\TypeInterventionController;
use App\Application\Http\Controllers\UniteController;
use App\Application\Http\Controllers\VehiculeController;

// Route spécial pour le serveur d'authentification
Route::group(['prefix' => 'v2', 'middleware' => [HttpLogger::class, JwtTokenValidatorAuth::class]], function () {
    Route::get('email-validate', [EmailController::class, 'validateEmail']);
});

// Route::get('exercices-comptable/{exercieComptableId}/justificatif', [CompteController::class, 'justificatifComplet']);
// Route::get('decomptes/{id}/print', [DecompteController::class, 'print']);
// Route::get('decomptes/{id}/print-par-sapeur', [DecompteController::class, 'printParSapeur']);
// Route::get('decomptes/{id}/print-par-compte', [DecompteController::class, 'printParCompte']);
Route::group(['prefix' => 'v2'], function () {
    Route::resource('sis-logo', SisLogoController::class)->only(['show']);
});

Route::group(['prefix' => 'v2', 'middleware' => [HttpLogger::class, DbSelector::class]], function () {

    // Mes infos
    Route::group(['middleware' => 'jwtTokenSapeur'], function () {
        Route::get('mes-infos', [MesInfosController::class, 'index'])->name('mes-infos');
        Route::get('mes-fonctions', [MesInfosController::class, 'fonctions'])->name('mes-fonctions');
        Route::get('mes-mutations', [MesInfosController::class, 'mutations'])->name('mes-mutations');
        Route::get('mes-grades', [MesInfosController::class, 'grades'])->name('mes-grades');
        Route::get('mes-cours', [MesInfosController::class, 'cours'])->name('mes-cours');
        Route::get('mes-groupes', [MesInfosController::class, 'groupes'])->name('mes-groupes');
        Route::get('mon-materiel', [MonMaterielController::class, 'index'])->name('mon-materiel');

        Route::get('mes-travaux/{exerciceComptableId}', [MesTravauxController::class, 'index'])->name('mes-travaux');
        Route::get('mes-exercices/{exerciceComptableId}', [MesExercicesController::class, 'index'])->name('mes-exercices');
        Route::get('mes-decomptes/{exerciceComptableId}', [MesDecomptesController::class, 'index'])->name('mes-decomptes');
        Route::get('mes-interventions/{exerciceComptableId}', [MesInterventionsController::class, 'index'])->name('mes-interventions');

        Route::get('mes-decomptes/{decompteId}/print', [MesDecomptesController::class, 'print'])->name('api.v2.mes-decomptes.print');
        Route::get('mon-certificat-salaire/{exerciceComptableId}', [MesDecomptesController::class, 'certificatSalaire'])->name('api.v2.mon-certificat-salaire');

        Route::post('mes-excuses/{exerciceId}', [MesExcusesController::class, 'store'])->name('api.v2.creer-excuse');
        Route::delete('mes-excuses/{exerciceId}', [MesExcusesController::class, 'delete'])->name('api.v2.delete-excuse');
        Route::get('mes-excuses/{exerciceId}/justificatif', [MesExcusesController::class, 'download'])->name('api.v2.download-excuse-justificatif');
    });

    // Paramètres accessible pour tout droit config
    Route::group(['middleware' => 'jwtTokenRole:utilisateur.config,sis.config,sapeur.config,organisation.modification,exercice.config,intervention.config,comptabilite.config,controle_medical.config'], function () {
        Route::resource('sis-param', SisParamController::class)->only(['index']);
    });

    // Sis Params
    Route::group(['middleware' => 'jwtTokenRole:sis.config'], function () {
        Route::resource('sis-param', SisParamController::class)->only(['store']);
        Route::resource('sis-logo', SisLogoController::class)->only(['store']);
        Route::post('localites-sis', [LocaliteSisController::class, 'store'])->name('api.v2.localite-sis-store');
        Route::delete('localites-sis', [LocaliteSisController::class, 'destroy'])->name('api.v2.localites-sis-destroy');
    });

    Route::group(['middleware' => 'jwtTokenRole:effectif.tout'], function () {
        Route::get('effectif', [SapeurController::class, 'effectif'])->name('api.v2.effectif');
        Route::get('liste-fssp', [SapeurController::class, 'listeFssp'])->name('api.v2.liste-fssp');
    });

    // All
    Route::group(['middleware' => 'jwtTokenSapeurOrRole'], function () {
        // Static Params Sapeurs ---
        Route::get('civilites', [CiviliteController::class, 'index'])->name('api.v2.civilites');
        Route::get('localites', [LocaliteController::class, 'index'])->name('api.v2.localites');
        Route::get('localites-sis', [LocaliteSisController::class, 'index'])->name('api.v2.localites-sis');
        Route::get('telephone-types', [TelephoneTypeController::class, 'index'])->name('api.v2.telephone-types');
        Route::get('permis', [PermisController::class, 'index'])->name('api.v2.permis');

        // Params Sapeurs
        Route::resource('grades', GradeController::class)->only(['index']);
        Route::resource('fonctions', FonctionController::class)->only(['index']);
        Route::resource('cours', CoursController::class)->only(['index']);

        // Interventions
        Route::resource('type-intervention', TypeInterventionController::class)->only(['index']);

        // Exercices
        Route::resource('exercice-categories', ExerciceCategorieController::class)->only(['index']);
        Route::resource('excuses-types', ExcuseTypeController::class)->only(['index']);

        // Heures supp
        Route::resource('heure-exercice-types', HeureExerciceTypeController::class)->only(['index']);
    });

    // Sapeurs
    Route::group(['middleware' => 'jwtTokenRole:sapeur.lecture,exercice.lecture,intervention.lecture,organisation.modification,comptabilite.tout,cours.lecture,mat_perso.lecture,fiche_travail.saisie_perso,fiche_travail.saisie_commune,fiche_travail.validation,fiche_travail.lecture'], function () {
        Route::resource('sapeurs', SapeurController::class)->only(['index', 'show']);

        Route::get('statistiques/{id}/civilite', [SapeurStatistiqueController::class, 'civilite']);
        Route::get('statistiques/{id}/fonction', [SapeurStatistiqueController::class, 'fonction']);
        Route::get('statistiques/{id}/grade', [SapeurStatistiqueController::class, 'grade']);
        Route::get('statistiques/{id}/permis', [SapeurStatistiqueController::class, 'permis']);
    });

    Route::group(['middleware' => 'jwtTokenRole:sapeur.lecture'], function () {
        Route::resource('sapeurs.permis', SapeurPermisController::class)->only(['index']);
        Route::resource('sapeurs.telephones', SapeurTelephoneController::class)->only(['index']);
        Route::resource('sapeurs.fonctions', SapeurFonctionController::class)->only(['index']);
        Route::resource('sapeurs.grades', SapeurGradeController::class)->only(['index']);
        Route::resource('sapeurs.mutations', SapeurMutationController::class)->only(['index']);
        Route::resource('sapeurs.materiels', SapeurMaterielController::class)->only(['index']);
        Route::resource('sapeurs.cours', SapeurCoursController::class)->only(['index']);
        Route::resource('sapeurs.photo', SapeurPhotoController::class)->only(['index']);
        Route::resource('sapeurs.groupes', SapeurGroupeController::class)->only(['index']);

        // Publipostage
        Route::post('publipostage', [PublipostageController::class, 'index'])->name('publipostage');

        Route::get('sapeurs/{id}/exercices/{exerciceComptableId}', [SapeurExerciceController::class, 'index']);
    });
    // Sapeurs
    Route::group(['middleware' => 'jwtTokenRole:sapeur.modification'], function () {
        Route::resource('sapeurs', SapeurController::class)->only(['store', 'update', 'destroy']);
        Route::resource('sapeurs.permis', SapeurPermisController::class)->only(['store', 'update', 'destroy']);
        Route::resource('sapeurs.telephones', SapeurTelephoneController::class)->only(['store', 'update', 'destroy']);
        Route::resource('sapeurs.fonctions', SapeurFonctionController::class)->only(['store', 'update', 'destroy']);
        Route::resource('sapeurs.grades', SapeurGradeController::class)->only(['store', 'update', 'destroy']);
        Route::resource('sapeurs.mutations', SapeurMutationController::class)->only(['store', 'update', 'destroy']);
        Route::resource('sapeurs.cours', SapeurCoursController::class)->only(['store', 'update', 'destroy']);
        Route::resource('sapeurs.photo', SapeurPhotoController::class)->only(['store', 'delete']);
        Route::delete('sapeurs/{sapeurId}/photo', [SapeurPhotoController::class, 'destroy'])->name('api.v2.sapeur.photo-destroy');

        // Route spécial pour les politiques et autres afin de changer leur statut
        Route::put('sapeurs/{id}/autre-statut', [SapeurController::class, 'autreStatut']);

        Route::post('sapeurs/{id}/fin-fonctions', [SapeurFonctionController::class, 'fin']);
        Route::post('sapeurs/{id}/quitter-groupes', [SapeurGroupeController::class, 'quitter']);
        Route::post('sapeurs/{id}/supprimer-convocations', [ConvocationsController::class, 'supprimerConvocations']);
    });

    // Params Sapeur
    Route::group(['middleware' => 'jwtTokenRole:sapeur.config'], function () {
        Route::resource('grades', GradeController::class)->only(['store', 'update', 'destroy']);
        Route::resource('fonctions', FonctionController::class)->only(['store', 'update', 'destroy']);
        Route::resource('cours', CoursController::class)->only(['store', 'update', 'destroy']);
    });

    // Organisation
    Route::group(['middleware' => 'jwtTokenRole:intervention.lecture,effectif.tout,sapeur.lecture,organisation.modification,comptabilite.tout'], function () {
        Route::resource('groupes', GroupeController::class)->only(['index']);
    });

    // Param organisation
    Route::group(['middleware' => 'jwtTokenRole:organisation.modification'], function () {
        Route::resource('groupes', GroupeController::class)->only(['store', 'update', 'destroy']);
        Route::resource('groupes.sapeurs', GroupeSapeursController::class)->only(['store']);

        Route::get('rta-gestsis', [ReferenceRtaController::class, 'getReferenceGestSis'])->name('api.v2.rta.get-gestsis');
        Route::get('rta', [ReferenceRtaController::class, 'getReferenceRta'])->name('api.v2.rta.get-rta');
        Route::post('rta', [ReferenceRtaController::class, 'setReference'])->name('api.v2.rta.set');
        Route::delete('rta', [ReferenceRtaController::class, 'resetReferenceRta'])->name('api.v2.rta.reset-rta');
    });

    // Exercices
    Route::group(['middleware' => 'jwtTokenSapeurOrRole:exercice.lecture,exercice.presence,comptabilite.tout'], function () {
        // Paramètres publiques car nécessaire pour le frontend
        Route::resource('excuse-param', ExcuseParamController::class)->only(['index']);
    });
    Route::group(['middleware' => 'jwtTokenRole:exercice.lecture,exercice.presence,comptabilite.tout'], function () {
        Route::resource('exercices', ExerciceController::class)->only(['index', 'show']);
        Route::resource('exercices.sapeurs', ConvocationsController::class)->only(['index']);

        Route::get('exercices/{id}/liste-presence', [ExerciceController::class, 'listePresence']);
        Route::get('exercices/{id}/liste-appel', [ExerciceController::class, 'listeAppel']);

        Route::get('exercices-absences/{exerciceComptableId}', [ExerciceController::class, 'absences']);

        // Convocations
        Route::get('convocation/{id}', [ConvocationController::class, 'convoquer']);

        // Statistiques
        Route::get('statistiques/{id}/presence', [SapeurExerciceController::class, 'stat']);
    });
    Route::group(['middleware' => 'jwtTokenRole:exercice.presence'], function () {
        Route::get('exercices-derniers', [ExerciceController::class, 'last'])->name('api.v2.exercices.derniers');

        Route::post('exercices/{id}/sapeurs', [ConvocationsController::class, 'store'])->name('api.v2.exercices.sapeurs.store');
        Route::delete('exercices/{id}/sapeurs', [ConvocationsController::class, 'destroy'])->name('api.v2.exercices.sapeurs.delete');

        // TODO: New routes
        Route::post('exercices/presence/{presenceId}', [ConvocationsController::class, 'updatePresence'])->name('api.v2.exercices.presence.update');
        // FIXME: Mobile
        Route::post('exercices/{id}/presences', [ConvocationsController::class, 'updatePresences'])->name('api.v2.exercices.presences');

        // Route::post('exercices/{convocationId}/excuses', [ExcuseController::class, 'store'])->name('api.v2.excuses.store');
        Route::delete('exercices/{exerciceId}/excuses/{sapeurId}', [ExcuseController::class, 'destroy'])->name('api.v2.excuses.delete');
        Route::get('exercices/{exerciceId}/excuses/{sapeurId}/justificatif', [ExcuseController::class, 'downloadJustificatif'])->name('api.v2.exercices.excuse.justificatif');

        // TODO: à implémenter
        // Route::get('exercices/{id}/liste-appel-localite', [ExerciceController::class, 'listeAppelLocalite']);
    });
    Route::group(['middleware' => 'jwtTokenRole:exercice.modification'], function () {
        Route::resource('exercices', ExerciceController::class)->only(['store', 'update']);
        Route::get('sapeurs-convocation', [SapeurController::class, 'convocationSms'])->name('sapeurs-convocation');
    });
    Route::group(['middleware' => 'jwtTokenRole:exercice.validation'], function () {
        Route::resource('exercices', ExerciceController::class)->only(['destroy']);
        Route::post('exercices/{id}/valider', [ExerciceController::class, 'valider'])->name('api.v2.exercices.valider');
        Route::post('exercices/{id}/annuler', [ExerciceController::class, 'annuler'])->name('api.v2.exercices.annuler');
        Route::post('exercices/{id}/reactiver', [ExerciceController::class, 'reactiver'])->name('api.v2.exercices.reactiver');
    });
    Route::group(['middleware' => 'jwtTokenRole:exercice.config'], function () {
        Route::resource('exercice-categories', ExerciceCategorieController::class)->only(['store', 'update', 'destroy']);
        Route::resource('excuses-types', ExcuseTypeController::class)->only(['store', 'update', 'destroy']);

        Route::resource('excuse-param', ExcuseParamController::class)->only(['store']);
    });

    // Fiche de travail
    Route::group(['middleware' => 'jwtTokenRole:fiche_travail.validation'], function () {
        Route::post('travaux/{id}/review', [TravailController::class, 'review'])->name('api.v2.travaux.review');
        Route::delete('travaux/{id}/review', [TravailController::class, 'cancelReview'])->name('api.v2.travaux.cancel-review');
    });
    Route::group(['middleware' => 'jwtTokenRole:fiche_travail.saisie_perso,fiche_travail.saisie_commune,fiche_travail.validation,fiche_travail.lecture'], function () {
        Route::resource('travail-types', TravailTypeController::class)->only(['index']);
        Route::resource('travaux', TravailController::class)->only(['index']);
        Route::get('travaux/{exerciceComptableId}', [TravailController::class, 'index'])->name('api.v2.travaux.index');
    });
    Route::group(['middleware' => 'jwtTokenRole:fiche_travail.saisie_perso,fiche_travail.saisie_commune,fiche_travail.validation'], function () {
        Route::resource('travaux', TravailController::class)->only(['store', 'update', 'destroy']);
    });
    Route::group(['middleware' => 'jwtTokenRole:fiche_travail.config'], function () {
        Route::resource('travail-types', TravailTypeController::class)->only(['store', 'update', 'destroy']);
    });

    // SMS
    Route::group(['middleware' => 'jwtTokenRole:sms.envoie'], function () {
        Route::get('sapeurs-telephones', [SapeurController::class, 'sapeursTelephones'])->name('sapeurs-telephones');
        Route::post('aspsms/send', [AspsmsController::class, 'send'])->name('aspsms-send');
        Route::get('aspsms/credit', [AspsmsController::class, 'credit'])->name('credit');
    });
    Route::group(['middleware' => 'jwtTokenRole:sms.config'], function () {
        Route::resource('aspsms/param', AspsmsParamController::class)->only(['index', 'store']);
    });

    // Interventions
    Route::group(['middleware' => 'jwtTokenRole:intervention.lecture,comptabilite.tout'], function () {
        Route::resource('interventions', InterventionController::class)->only(['index', 'show']);
        Route::resource('interventions.materiels', InterventionMaterielsController::class)->only(['index']);
        Route::resource('interventions.vehicules', InterventionVehiculesController::class)->only(['index']);
        Route::resource('interventions.missions', InterventionMissionsController::class)->only(['index']);
        Route::resource('interventions.appels', InterventionAppelsController::class)->only(['index']);
        Route::resource('interventions.sapeurs', InterventionSapeursController::class)->only(['index']);
        Route::resource('interventions.quittances', InterventionQuittancesController::class)->only(['index']);
        Route::resource('interventions.groupes', InterventionGroupesController::class)->only(['index']);
        Route::resource('interventions.phases', InterventionPhasesController::class)->only(['index']);

        // Impressions interventions
        Route::get('interventions/{id}/rapport', [InterventionController::class, 'rapport'])->name('api.v2.interventions.rapport');

        // Statistiques véhicule et matériel
        Route::get('statistiques/{id}/materiel', [InterventionStatistiqueController::class, 'materiel']);
        Route::get('statistiques/{id}/vehicule', [InterventionStatistiqueController::class, 'vehicule']);
        Route::get('statistiques/{id}/type-intervention', [InterventionStatistiqueController::class, 'typeIntervention']);
        Route::get('statistiques/{id}/stat-federal', [InterventionStatistiqueController::class, 'statFederal']);
        Route::get('statistiques/{id}/intervention-traitement', [InterventionStatistiqueController::class, 'traitement']);
    });
    Route::group(['middleware' => 'jwtTokenRole:intervention.modification'], function () {
        Route::resource('alarmes', AlarmeController::class)->only(['index']);

        Route::resource('interventions', InterventionController::class)->only(['store', 'update', 'destroy']);
        Route::post('interventions-complet', [InterventionController::class, 'complet'])->name('complet');

        Route::post('interventions/{id}/materiels', [InterventionMaterielsController::class, 'store'])->name('api.v2.interventions.materiels.store');
        Route::put('interventions/{id}/materiels', [InterventionMaterielsController::class, 'update'])->name('api.v2.interventions.materiels.update');
        Route::delete('interventions/{id}/materiels', [InterventionMaterielsController::class, 'destroy'])->name('api.v2.interventions.materiels.delete');

        Route::post('interventions/{id}/vehicules', [InterventionVehiculesController::class, 'store'])->name('api.v2.interventions.vehicules.store');
        Route::delete('interventions/{id}/vehicules', [InterventionVehiculesController::class, 'destroy'])->name('api.v2.interventions.vehicules.delete');

        Route::post('interventions/{id}/missions', [InterventionMissionsController::class, 'store'])->name('api.v2.interventions.missions.store');
        Route::put('interventions/{id}/missions', [InterventionMissionsController::class, 'update'])->name('api.v2.interventions.missions.update');
        Route::delete('interventions/{id}/missions', [InterventionMissionsController::class, 'destroy'])->name('api.v2.interventions.missions.delete');

        Route::post('interventions/{id}/appels', [InterventionAppelsController::class, 'store'])->name('api.v2.interventions.appels.store');
        Route::put('interventions/{id}/appels', [InterventionAppelsController::class, 'update'])->name('api.v2.interventions.appels.update');
        Route::delete('interventions/{id}/appels', [InterventionAppelsController::class, 'destroy'])->name('api.v2.interventions.appels.delete');

        Route::post('interventions/{id}/sapeurs', [InterventionSapeursController::class, 'store'])->name('api.v2.interventions.sapeurs.store');
        Route::put('interventions/{id}/sapeurs', [InterventionSapeursController::class, 'update'])->name('api.v2.interventions.sapeurs.update');
        Route::delete('interventions/{id}/sapeurs', [InterventionSapeursController::class, 'destroy'])->name('api.v2.interventions.sapeurs.delete');

        Route::post('interventions/{id}/quittances', [InterventionQuittancesController::class, 'store'])->name('api.v2.interventions.quittances.store');
        Route::delete('interventions/{id}/quittances', [InterventionQuittancesController::class, 'destroy'])->name('api.v2.interventions.quittances.delete');

        Route::post('interventions/{id}/groupes', [InterventionGroupesController::class, 'store'])->name('api.v2.interventions.groupes.store');
        Route::delete('interventions/{id}/groupes', [InterventionGroupesController::class, 'destroy'])->name('api.v2.interventions.groupes.delete');

        Route::post('interventions/{id}/phases', [InterventionPhasesController::class, 'store'])->name('api.v2.interventions.phases.store');
        Route::put('interventions/{id}/phases', [InterventionPhasesController::class, 'update'])->name('api.v2.interventions.phases.update');
        Route::delete('interventions/{id}/phases', [InterventionPhasesController::class, 'destroy'])->name('api.v2.interventions.phases.delete');
    });
    Route::group(['middleware' => 'jwtTokenRole:intervention.validation'], function () {
        Route::post('interventions/{id}/valider', [InterventionController::class, 'valider'])->name('api.v2.interventions.valider');
    });
    Route::group(['middleware' => 'jwtTokenRole:intervention.lecture,intervention.modification,comptabilite.tout'], function () {
        // TODO: see to add the correct right for the following routes : 'store', 'update']);
        Route::resource('phase-types', PhaseTypeController::class)->only(['index']);
        Route::resource('stat-federal', StatFederalController::class)->only(['index']);
        Route::resource('stat-intervention', StatInterventionController::class)->only(['index']);
        Route::resource('intervention-traitement', InterventionTraitementController::class)->only(['index']);
    });
    Route::group(['middleware' => 'jwtTokenRole:intervention.lecture'], function () {
        Route::resource('vehicules', VehiculeController::class)->only(['index']);
        Route::resource('materiels', MaterielController::class)->only(['index']);
        Route::resource('mission-types', MissionTypeController::class)->only(['index']);
        Route::resource('telephones', TelephoneController::class)->only(['index']);
    });
    Route::group(['middleware' => 'jwtTokenRole:intervention.config'], function () {
        Route::resource('type-intervention', TypeInterventionController::class)->only(['store', 'update', 'destroy']);
        Route::resource('vehicules', VehiculeController::class)->only(['store', 'update', 'destroy']);
        Route::resource('materiels', MaterielController::class)->only(['store', 'update', 'destroy']);
        Route::resource('stat-intervention', StatInterventionController::class)->only(['store', 'update', 'destroy']);
        Route::resource('intervention-traitement', InterventionTraitementController::class)->only(['store', 'update', 'destroy']);
        Route::resource('telephones', TelephoneController::class)->only(['store', 'update', 'destroy']);
        Route::resource('mission-types', MissionTypeController::class)->only(['store', 'update', 'destroy']);
    });

    // Exercices comptables
    Route::group(['middleware' => 'jwtTokenSapeurOrRole'], function () {
        Route::resource('exercices-comptable', ExerciceComptableController::class)->only(['index']);

        // Static param Comptabilite
        Route::get('unites', [UniteController::class, 'index'])->name('api.v2.unites');
    });

    Route::group(['middleware' => 'jwtTokenRole:cours.lecture,comptabilite.tout'], function () {
        Route::get('cours-sapeurs/{exerciceComptableId}', [CoursSapeurController::class, 'index']);
    });

    // Comptabilite
    Route::group(['middleware' => 'jwtTokenRole:comptabilite.tout'], function () {
        Route::post('imputation/annuel/{id}', [ImputationController::class, 'annuel']);
        Route::delete('imputation/annuel/{id}', [ImputationController::class, 'cancelAnnuel']);
        Route::post('imputation/cours/{id}', [ImputationController::class, 'cours']);
        Route::delete('imputation/cours/{id}', [ImputationController::class, 'cancelCours']);
        Route::post('imputation/travail', [ImputationController::class, 'travail']);
        Route::delete('imputation/travail/{id}', [ImputationController::class, 'cancelTravail']);
        Route::post('imputation/exercice/{id}', [ImputationController::class, 'exercice']);
        Route::delete('imputation/exercice/{id}', [ImputationController::class, 'cancelExercice']);
        Route::post('imputation/intervention/{id}', [ImputationController::class, 'intervention']);
        Route::delete('imputation/intervention/{id}', [ImputationController::class, 'cancelIntervention']);

        Route::get('ecritures/exercices/{id}', [EcritureController::class, 'exercices']);
        Route::get('ecritures/exercice/{id}', [EcritureController::class, 'exercice']);
        Route::get('ecritures/annuel/{id}', [EcritureController::class, 'annuel']);
        Route::get('ecritures/amende/{id}', [EcritureController::class, 'amende']);
        Route::get('ecritures/intervention/{id}', [EcritureController::class, 'intervention']);
        Route::get('ecritures/divers/{id}', [EcritureController::class, 'divers']);
        Route::get('ecritures/{id}', [EcritureController::class, 'all']);

        Route::resource('ecritures', EcritureController::class)->only(['store', 'update', 'destroy']);

        Route::get('comptes/{id}/ecritures/{exerciceComptableId}', [CompteController::class, 'ecritures']);

        Route::get('exercices-comptable/{exercieComptableId}/comptes/{compteId}/justificatif', [CompteController::class, 'justificatifIndividuel']);
        Route::get('exercices-comptable/{exercieComptableId}/justificatif', [CompteController::class, 'justificatifComplet']);

        // Params Comptabilite
        Route::resource('comptes', CompteController::class)->only(['index']);
        Route::resource('indemnites-exercice-types', IndemniteExerciceTypeController::class)->only(['index']);
        Route::resource('indemnites-intervention-types', IndemniteInterventionTypeController::class)->only(['index']);
        Route::resource('indemnites-cours-types', IndemniteCoursTypeController::class)->only(['index']);
        Route::resource('frais-indemnites-types', FraisIndemniteTypeController::class)->only(['index']);
        Route::resource('frais-indemnites-annuel-types', FraisIndemniteAnnuelTypeController::class)->only(['index']);
        Route::resource('ecriture-categories', EcritureCategorieController::class)->only(['index']);

        // Décomptes
        Route::get('decomptes/{id}/ecritures', [DecompteController::class, 'ecritures']);
        Route::post('decomptes/creer-annuel', [DecompteController::class, 'creerAnnuel']);
        Route::post('decomptes/creer-sapeur', [DecompteController::class, 'creerSapeur']);
        Route::post('decomptes/creer-exercice', [DecompteController::class, 'creerExercice']);
        Route::get('decomptes/exercice-comptable/{id}', [DecompteController::class, 'getByExerciceComptable']);
        Route::get('decomptes/{id}/iso20022', [DecompteController::class, 'iso20022']);
        Route::get('decomptes/{id}/a-facturer', [DecompteController::class, 'aFacturer']);
        Route::get('decomptes/{id}/export-ecritures', [DecompteController::class, 'exportEcritures']);
        Route::get('decomptes/{id}/print', [DecompteController::class, 'print']);
        Route::get('decomptes/{id}/print-par-sapeur', [DecompteController::class, 'printParSapeur']);
        Route::get('decomptes/{id}/print-par-sapeur/{sapeurId}', [DecompteController::class, 'printPourSapeur']);
        Route::get('decomptes/{id}/print-par-compte', [DecompteController::class, 'printParCompte']);
        Route::resource('decomptes', DecompteController::class)->only(['show', 'destroy']);

        // Params Amendes
        Route::resource('amendes', AmendeController::class)->only(['index']);

        // Paiements
        Route::get('paiements/exercice-comptable/{id}', [PaiementController::class, 'getByExerciceComptable']);
        Route::get('paiements/{id}/iso20022', 'PaiementController@iso20022');
        Route::get('paiements/{id}', [PaiementController::class, 'get']);

        // Amendes
        Route::post('generer-amendes/{id}/sapeur/{sapeurId}', [AmendeController::class, 'sapeur']);
        Route::post('generer-amendes/{id}', [AmendeSapeurController::class, 'annuel']);

        // Certificats de salaire
        Route::get('exercices-comptable/{ExerciceComptableId}/certificat-salaire', [DecompteController::class, 'certificatSalaire']);
        Route::get('exercices-comptable/{ExerciceComptableId}/certificat-salaire/{SapeurId}', [DecompteController::class, 'certificatSalaireSapeur']);

        // Statistiques
        Route::get('statistiques/{id}/categorie-comptable', [ComptabiliteStatistiqueController::class, 'categorie']);
        Route::get('statistiques/{id}/compte', [ComptabiliteStatistiqueController::class, 'compte']);
        Route::get('statistiques/{id}/module-comptable', [ComptabiliteStatistiqueController::class, 'module']);
    });

    Route::group(['middleware' => 'jwtTokenRole:comptabilite.config'], function () {
        //TODO: Suppression d'exercices comptable ???
        Route::resource('exercices-comptable', ExerciceComptableController::class)->only(['store', 'update']);
        //TODO: Regarder que faire avec cloturer, déjà modifiable via la route update pour le moment
        // Route::post('exercice-comptable/{id}/cloturer', [ExerciceComptableController::class, 'cloturer']);

        Route::resource('avs-param', AvsParamController::class)->only(['index', 'store']);

        Route::resource('comptes', CompteController::class)->only(['store', 'update', 'destroy']);
        Route::resource('indemnites-exercice-types', IndemniteExerciceTypeController::class)->only(['store', 'update', 'destroy']);
        Route::resource('indemnites-intervention-types', IndemniteInterventionTypeController::class)->only(['store', 'update', 'destroy']);
        Route::resource('frais-indemnites-annuel', FraisIndemniteAnnuelController::class)->only(['store', 'update', 'destroy']);
        Route::resource('frais-indemnites-annuel-types', FraisIndemniteAnnuelTypeController::class)->only(['store', 'update', 'destroy']);
        Route::resource('ecriture-categories', EcritureCategorieController::class)->only(['store', 'update', 'destroy']);

        Route::resource('amendes', AmendeController::class)->only(['store']);
        Route::resource('heure-exercice-types', HeureExerciceTypeController::class)->only(['store', 'update', 'destroy']);

        Route::resource('indemnites-cours-types', IndemniteCoursTypeController::class)->only(['store', 'update', 'destroy']);
    });

    // Controles médicaux
    Route::group(['middleware' => 'jwtTokenRole:controle_medical.tout'], function () {
        Route::resource('controles-medicaux', ControleMedicalController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::get('controles-medicaux/{id}/justificatif', [JustificatifController::class, 'show']);
        Route::post('controles-medicaux/{id}/justificatif', [JustificatifController::class, 'store']);
        Route::delete('controles-medicaux/{id}/justificatif', [JustificatifController::class, 'destroy']);

        // Params Controles médicaux
        Route::resource('medecins', MedecinController::class)->only(['index']);
        Route::resource('controles-medicaux-types', ControleMedicalTypeController::class)->only(['index']);

        // Controles médicaux pour sapeur
        Route::resource('sapeurs.controles-medicaux', SapeurControleMedicalController::class)->only(['index']);
    });

    Route::group(['middleware' => 'jwtTokenRole:controle_medical.config'], function () {
        Route::resource('medecins', MedecinController::class)->only(['store', 'update', 'destroy']);
        Route::resource('controles-medicaux-types', ControleMedicalTypeController::class)->only(['store', 'update', 'destroy']);
    });


    // Matériel personnel
    Route::group(['middleware' => 'jwtTokenRole:mat_perso.lecture'], function () {
        Route::resource('mat-perso-categories', MatPersoCategorieController::class)->only(['index']);
        Route::resource('mat-perso-types', MatPersoTypeController::class)->only(['index']);
        Route::resource('mat-perso-event-types', MatPersoEventTypeController::class)->only(['index']);
        Route::resource('mat-perso-alerte-types', MatPersoAlerteTypeController::class)->only(['index']);

        Route::resource('mat-perso', MatPersoController::class)->only(['index']);
        Route::get('mat-perso/a-recuperer', [MatPersoController::class, 'aRecuperer'])->name('mat-perso.a-recuperer');
        Route::resource('mat-perso-alertes', MatPersoAlerteController::class)->only(['index']);
    });

    Route::group(['middleware' => 'jwtTokenRole:mat_perso.modification'], function () {
        Route::post('mat-perso/attribuer', [MatPersoAttributionController::class, 'attribuer'])->name('mat-perso.attribuer');
        Route::post('mat-perso/retour', [MatPersoAttributionController::class, 'retour'])->name('mat-perso.retour');

        // Modifier matériel
        Route::post('mat-perso', [MatPersoController::class, 'create'])->name('mat-perso.create');
        Route::put('mat-perso', [MatPersoController::class, 'update'])->name('mat-perso.update');
        Route::delete('mat-perso', [MatPersoController::class, 'destroy'])->name('mat-perso.drestroy');

        // Events
        Route::post('mat-perso-event', [MatPersoEventController::class, 'create'])->name('mat-perso-event.create');
    });

    Route::group(['middleware' => 'jwtTokenRole:mat_perso.config'], function () {
        Route::resource('mat-perso-categories', MatPersoCategorieController::class)->only(['store', 'update', 'destroy']);
        Route::resource('mat-perso-types', MatPersoTypeController::class)->only(['store', 'update', 'destroy']);
        Route::resource('mat-perso-event-types', MatPersoEventTypeController::class)->only(['store', 'update', 'destroy']);
        Route::resource('mat-perso-alerte-types', MatPersoAlerteTypeController::class)->only(['store', 'update', 'destroy']);
    });

    // TODO: Ajouter route type d'unité
    // (optionnel) Liste des dernières interventions (30 derniers jours)
});
