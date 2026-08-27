<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use App\Domaine\Business\Materiel\ArticleBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Emplacement;
use App\Models\Hangar;
use App\Models\InterventionVehicule;
use App\Models\MaterielType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * TEMPORAIRE : contrôleur de migration pour la fonctionnalité article-emplacement
 * (véhicules et hangars). Toute la logique de migration est volontairement gardée
 * ici plutôt que dans les classes Business, pour ne pas les alourdir avec du code
 * transitoire. À supprimer une fois la migration des données existantes terminée
 * (voir routes/api.php).
 */
class MigrationMaterielController extends Controller
{
    /**
     * Emplacements n'ayant pas encore de hangar lié (candidats à transformer).
     */
    public function emplacementsSansHangar()
    {
        $emplacements = Emplacement::with(['article', 'hangar'])
            ->whereDoesntHave('hangar')
            ->whereNull('article_id')
            ->orderBy('designation')
            ->get();

        return response()->json(['data' => $emplacements]);
    }

    /**
     * Transforme un emplacement existant en hangar (attache les champs
     * d'adresse), sans toucher aux autres champs de l'emplacement.
     */
    public function transformerEnHangar(Request $request, $id)
    {
        $emplacement = Emplacement::findOrFail($id);
        if ($emplacement->hangar !== null) {
            throw new ArrayException([], "Cet emplacement est déjà un hangar");
        }

        $data = $request->validate([
            'rue' => 'string|nullable',
            'no_rue' => 'string|nullable',
            'localite_id' => 'integer|required',
        ]);
        // Le middleware ConvertEmptyStringsToNull transforme les champs texte vides
        // en null ; rue/no_rue sont NOT NULL (avec défaut '') en base.
        $data['rue'] ??= '';
        $data['no_rue'] ??= '';

        Hangar::create(['id' => $emplacement->id, ...$data]);

        return response()->json(['data' => Emplacement::with(['article', 'hangar'])->find($id)]);
    }

    /**
     * Articles est_emplacement (véhicules) sans emplacement représenté.
     */
    public function vehiculesSansEmplacement()
    {
        $estEmplacementIds = MaterielType::where('est_emplacement', true)->pluck('id');

        $articles = ArticleBusiness::getAllArticles()
            ->filter(fn($article) => $estEmplacementIds->contains($article->materiel_type_id))
            ->filter(fn($article) => $article->emplacementRepresentee === null)
            ->values();

        return response()->json(['data' => $articles]);
    }

    /**
     * Fusionne un véhicule existant avec un emplacement existant (ex: l'emplacement
     * créé manuellement par convention de nommage avant cette fonctionnalité) :
     * l'emplacement devient celui représenté par l'article, sans en recréer un neuf.
     */
    public function lierEmplacement(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        if ($article->emplacementRepresentee !== null) {
            throw new ArrayException([], "Cet article a déjà un emplacement lié");
        }
        $type = MaterielType::findOrFail($article->materiel_type_id);
        if (!$type->est_emplacement) {
            throw new ArrayException([], "Cet article n'est pas d'un type qui représente un emplacement");
        }

        $data = $request->validate([
            'emplacement_id' => 'integer|required',
        ]);

        $emplacement = Emplacement::findOrFail($data['emplacement_id']);
        if ($emplacement->article_id !== null) {
            throw new ArrayException([], "Cet emplacement est déjà lié à un autre article");
        }

        $emplacement->update([
            'article_id' => $article->id,
            'designation' => $article->designation,
            'remarque' => $article->remarque,
        ]);

        // Un article est_emplacement n'a pas son propre emplacement_id (sa position
        // est portée par le parent_id de l'emplacement qu'il représente) ; un véhicule
        // antérieur à cette fonctionnalité a encore l'ancien emplacement_id où il
        // était "rangé".
        $article->update(['emplacement_id' => null]);

        return response()->json(['data' => Article::with('emplacementRepresentee')->find($id)]);
    }

    /**
     * Crée un nouvel article véhicule à partir d'un emplacement existant sans
     * article (ex: un véhicule dont seul l'emplacement a été historiquement suivi,
     * sans fiche article correspondante).
     */
    public function convertirEnVehicule(Request $request, $id)
    {
        $emplacement = Emplacement::findOrFail($id);
        if ($emplacement->article_id !== null) {
            throw new ArrayException([], "Cet emplacement est déjà lié à un article");
        }

        $data = $request->validate([
            'materiel_type_id' => 'integer|required',
            'immatriculation' => 'string|nullable',
            'chassis' => 'string|nullable',
        ]);
        // Le middleware ConvertEmptyStringsToNull transforme les champs texte vides
        // en null ; immatriculation/chassis sont NOT NULL (avec défaut '') en base.
        $data['immatriculation'] ??= '';
        $data['chassis'] ??= '';

        $type = MaterielType::findOrFail($data['materiel_type_id']);
        if (!$type->est_emplacement) {
            throw new ArrayException([], "Ce type de matériel ne représente pas un emplacement");
        }

        $article = Article::create([
            'materiel_type_id' => $type->id,
            'uuid' => uniqid(),
            'designation' => $emplacement->designation,
            'remarque' => $emplacement->remarque,
            'immatriculation' => $data['immatriculation'],
            'chassis' => $data['chassis'],
            'emplacement_id' => null,
            'sapeur_id' => null,
            'statut' => true,
        ]);

        $emplacement->update(['article_id' => $article->id]);

        return response()->json(['data' => Emplacement::with(['article', 'hangar'])->find($id)]);
    }

    /**
     * Fusionne 2 véhicules dupliqués (ex: créés séparément avant cette
     * fonctionnalité) : le véhicule "supprimé" est retiré, ses sous-emplacements
     * et articles rangés sont déplacés sous le véhicule "conservé", et ses
     * éventuels rattachements à des interventions sont reportés sur celui-ci
     * (sans créer de doublon si l'intervention était déjà liée aux deux).
     */
    public function fusionnerVehicules(Request $request)
    {
        $data = $request->validate([
            'vehicule_conserve_id' => 'integer|required',
            'vehicule_supprime_id' => 'integer|required|different:vehicule_conserve_id',
        ]);

        $conserve = Article::with('emplacementRepresentee')->findOrFail($data['vehicule_conserve_id']);
        $supprime = Article::with('emplacementRepresentee')->findOrFail($data['vehicule_supprime_id']);

        $typesEstEmplacement = MaterielType::where('est_emplacement', true)->pluck('id');
        if (
            !$typesEstEmplacement->contains($conserve->materiel_type_id) ||
            !$typesEstEmplacement->contains($supprime->materiel_type_id)
        ) {
            throw new ArrayException([], "Les deux articles doivent être d'un type qui représente un emplacement");
        }
        if ($conserve->emplacementRepresentee === null || $supprime->emplacementRepresentee === null) {
            throw new ArrayException([], "Les deux véhicules doivent avoir un emplacement lié, utilisez l'outil de migration pour le créer");
        }

        $emplacementConserveId = $conserve->emplacementRepresentee->id;
        $emplacementSupprimeId = $supprime->emplacementRepresentee->id;

        DB::transaction(function () use ($conserve, $supprime, $emplacementConserveId, $emplacementSupprimeId) {
            // Reporte les rattachements aux interventions du véhicule supprimé sur
            // celui conservé, sauf si l'intervention est déjà liée aux deux (la
            // contrainte unique ['vehicule_id', 'intervention_id'] interdirait le
            // doublon) : ceux-là sont simplement supprimés.
            $interventionIdsDejaLiees = InterventionVehicule::where('vehicule_id', $conserve->id)
                ->pluck('intervention_id');
            InterventionVehicule::where('vehicule_id', $supprime->id)
                ->whereNotIn('intervention_id', $interventionIdsDejaLiees)
                ->update(['vehicule_id' => $conserve->id]);
            InterventionVehicule::where('vehicule_id', $supprime->id)->delete();

            Emplacement::where('parent_id', $emplacementSupprimeId)
                ->update(['parent_id' => $emplacementConserveId]);
            Article::where('emplacement_id', $emplacementSupprimeId)
                ->update(['emplacement_id' => $emplacementConserveId]);

            // L'emplacement supprimé doit être retiré avant l'article : sa colonne
            // article_id référence articles.id.
            Emplacement::destroy($emplacementSupprimeId);
            Article::destroy($supprime->id);
        });

        return response()->json(['data' => Article::with('emplacementRepresentee')->find($conserve->id)]);
    }
}
