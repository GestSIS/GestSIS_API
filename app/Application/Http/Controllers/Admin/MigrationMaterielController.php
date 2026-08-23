<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use App\Domaine\Business\Materiel\ArticleBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Emplacement;
use App\Models\Hangar;
use App\Models\MaterielType;
use Illuminate\Http\Request;

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

        return response()->json(['data' => Article::with('emplacementRepresentee')->find($id)]);
    }
}
