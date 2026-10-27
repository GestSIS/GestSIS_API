<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Business\TravauxBusiness;
use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\Intervention;
use App\Models\InterventionSapeur;
use App\Models\Travail;
use Illuminate\Http\Request;

class TravailController extends Controller
{
    public function index(Request $request, $exerciceComptableId)
    {
        // Auteur
        $perms = $request->attributes->get('permissions', []);
        $hasLectureOuValidationPermission = in_array('fiche_travail.validation', $perms) || in_array('fiche_travail.lecture', $perms);
        $withEcritures = in_array('comptabilite.lecture', $perms);

        $sapeurId = $request->attributes->get('sapeurId');
        if (!$hasLectureOuValidationPermission && !$sapeurId) {
            return response()->json(['message' => 'Permissions insuffisantes'], 403);
        }

        $query = Travail::with(['intervention:id,date_debut,objet,statut', 'exercice:id,date,designation,statut'])->where('exercice_comptable_id', '=', $exerciceComptableId);
        if ($withEcritures) {
            $query = $query->with('ecritures');
        }
        if (!$hasLectureOuValidationPermission && $sapeurId != null) {
            $query = $query->where(function ($query) use ($sapeurId) {
                $query->where('auteur_id', '=', $sapeurId)
                    ->orWhere('sapeur_id', '=', $sapeurId);
            });
        }
        $travaux = $query->get();

        return response()->json(['data' => $travaux]);
    }

    /**
     * Fiches de travail liées à un exercice.
     */
    public function parExercice(int $id)
    {
        $travaux = Travail::with('travailType')->where('exercice_id', $id)->get();
        return response()->json(['data' => $travaux]);
    }

    /**
     * Fiches de travail liées à une intervention.
     */
    public function parIntervention(int $id)
    {
        $travaux = Travail::with('travailType')->where('intervention_id', $id)->get();
        return response()->json(['data' => $travaux]);
    }

    /**
     * Exercices et interventions ouverts (ni imputés, ni annulés) auxquels l'utilisateur peut lier une fiche :
     * tous avec le droit de lecture du module, sinon ceux où il figure (convocation ou présence).
     */
    public function evenements(Request $request, int $exerciceComptableId)
    {
        $perms = $request->attributes->get('permissions', []);
        $sapeurId = $request->attributes->get('sapeurId');

        $interventions = Intervention::where('exercice_comptable_id', $exerciceComptableId)
            ->where('statut', '!=', InterventionBusiness::INTERVENTION_STATUT_IMPUTE);
        if (!in_array('intervention.lecture', $perms)) {
            $interventions->whereIn('id', InterventionSapeur::where('sapeur_id', $sapeurId)->select('intervention_id'));
        }

        $exercices = Exercice::where('exercice_comptable_id', $exerciceComptableId)
            ->whereNotIn('statut', [ExerciceBusiness::EXERCICE_STATUT_ANNULE, ExerciceBusiness::EXERCICE_STATUT_IMPUTE]);
        if (!in_array('exercice.lecture', $perms)) {
            $exercices->whereIn('id', ExerciceSapeur::where('sapeur_id', $sapeurId)->select('exercice_id'));
        }

        return response()->json(['data' => [
            'interventions' => $interventions->orderByDesc('date_debut')->get(['id', 'date_debut', 'objet', 'statut']),
            'exercices' => $exercices->orderByDesc('date')->get(['id', 'date', 'designation', 'statut']),
        ]]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'travaux' => 'array',
            'travaux.*.designation' => 'required|string|min:1',
            'travaux.*.date' => 'required|date',
            'travaux.*.quantite' => 'required|numeric',
            'travaux.*.sapeur_id' => 'required|integer|exists:sapeurs,id',
            'travaux.*.travail_type_id' => 'required|integer|exists:travail_types,id',
            'travaux.*.exercice_comptable_id' => 'required|integer|exists:exercice_comptables,id',
            'travaux.*.exercice_id' => 'nullable|integer|exists:exercices,id',
            'travaux.*.intervention_id' => 'nullable|integer|exists:interventions,id',
        ]);

        // Auteur
        $auteurId = $request->attributes->get('sapeurId');
        if (!$auteurId) {
            return response()->json(['message' => 'Permissions insuffisantes'], 403);
        }
        $perms = $request->attributes->get('permissions', []);

        $hasSaisieCommunePermission = in_array('fiche_travail.saisie_commune', $perms);

        $travail = TravauxBusiness::ajouter(
            $data['travaux'],
            $auteurId,
            $hasSaisieCommunePermission,
            in_array('exercice.validation', $perms),
            in_array('intervention.validation', $perms)
        );
        return response()->json(['data' => $travail], 201);
    }

    public function update(Request $request, $travailId)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'date' => 'required|date',
            'quantite' => 'required|numeric',
            'sapeur_id' => 'required|integer|exists:sapeurs,id',
            'travail_type_id' => 'required|integer|exists:travail_types,id',
            'exercice_id' => 'nullable|integer|exists:exercices,id',
            'intervention_id' => 'nullable|integer|exists:interventions,id',
        ]);

        $sapeurId = $request->attributes->get('sapeurId');
        $perms = $request->attributes->get('permissions', []);

        $travail = TravauxBusiness::modifier(
            $travailId,
            $data,
            $sapeurId,
            in_array('exercice.validation', $perms),
            in_array('intervention.validation', $perms)
        );
        return response()->json(['data' => $travail]);
    }

    public function review(Request $request, $travailId)
    {
        $data = $request->validate([
            'justification' => 'string|nullable',
            'accepte' => 'boolean|required',
            'quantite' => 'numeric|required',
        ]);

        $perms = $request->attributes->get('permissions', []);
        $travail = TravauxBusiness::review(
            $travailId,
            $data['accepte'],
            $data['justification'] ?? '',
            $data['quantite'],
            in_array('fiche_travail.validation', $perms),
            in_array('exercice.validation', $perms),
            in_array('intervention.validation', $perms)
        );
        return response()->json(['data' => $travail]);
    }

    public function cancelReview(Request $request, $travailId)
    {
        $perms = $request->attributes->get('permissions', []);
        $travail = TravauxBusiness::cancelReview(
            $travailId,
            in_array('fiche_travail.validation', $perms),
            in_array('exercice.validation', $perms),
            in_array('intervention.validation', $perms)
        );
        return response()->json(['data' => $travail]);
    }

    public function destroy(Request $request, $id)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if (!$sapeurId) {
            return response()->json(['message' => 'Permissions insuffisantes'], 403);
        }

        $perms = $request->attributes->get('permissions', []);
        $travail = TravauxBusiness::supprimer(
            $id,
            $sapeurId,
            in_array('exercice.validation', $perms),
            in_array('intervention.validation', $perms)
        );
        return response()->json(null, 204);
    }
}
