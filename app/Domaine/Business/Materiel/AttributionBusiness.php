<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\ArticleAttribution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Historique des attributions d'articles aux sapeurs.
 *
 * Tout changement de détenteur (`articles.sapeur_id`) passe par `ouvrir` /
 * `cloturer`, dans la même transaction que l'écriture de l'attribution.
 */
class AttributionBusiness
{
    /**
     * Ouvre une attribution de l'article au sapeur à la date donnée et en fait
     * son détenteur (l'article quitte son emplacement).
     *
     * @throws ArrayException si la date est future, si l'article est déjà attribué
     *                        ou si la date précède la fin de la dernière attribution close
     */
    public static function ouvrir(Article $article, int $sapeurId, string $date, ?int $auteurSapeurId): ArticleAttribution
    {
        $startDate = Carbon::parse($date)->startOfDay();
        self::assertPasDansLeFutur($startDate);

        if ($article->attributionOuverte()->exists()) {
            throw new ArrayException([], "L'article {$article->id} est déjà attribué, rendez-le avant de l'attribuer à nouveau");
        }

        $lastEnd = $article->attributions()->whereNotNull('fin')->max('fin');
        if ($lastEnd !== null && $startDate->lt(Carbon::parse($lastEnd))) {
            $formattedEnd = Carbon::parse($lastEnd)->format('d.m.Y');
            throw new ArrayException([], "L'attribution ne peut pas commencer avant la fin de la précédente ({$formattedEnd})");
        }

        return DB::transaction(function () use ($article, $sapeurId, $startDate, $auteurSapeurId) {
            $attribution = ArticleAttribution::create([
                'article_id' => $article->id,
                'sapeur_id' => $sapeurId,
                'debut' => $startDate->toDateString(),
                'fin' => null,
                'attribue_par_sapeur_id' => $auteurSapeurId,
            ]);

            $article->update(['sapeur_id' => $sapeurId, 'emplacement_id' => null]);

            return $attribution;
        });
    }

    /**
     * Clôt l'attribution ouverte de l'article à la date donnée : l'article n'a plus
     * de détenteur et, si un emplacement est fourni, y est rangé.
     * Sans attribution ouverte, seul le rangement est appliqué.
     *
     * @throws ArrayException si la date est future ou précède le début de l'attribution
     */
    public static function cloturer(Article $article, string $date, ?int $emplacementRetourId, ?int $auteurSapeurId): void
    {
        $endDate = Carbon::parse($date)->startOfDay();
        self::assertPasDansLeFutur($endDate);

        $attribution = $article->attributionOuverte()->first();
        if ($attribution !== null && $endDate->lt($attribution->debut)) {
            $formattedStart = $attribution->debut->format('d.m.Y');
            throw new ArrayException([], "L'attribution ne peut pas se terminer avant son début ({$formattedStart})");
        }

        DB::transaction(function () use ($article, $attribution, $endDate, $emplacementRetourId, $auteurSapeurId) {
            $attribution?->update([
                'fin' => $endDate->toDateString(),
                'emplacement_retour_id' => $emplacementRetourId,
                'cloture_par_sapeur_id' => $auteurSapeurId,
            ]);

            $articleData = ['sapeur_id' => null];
            if ($emplacementRetourId !== null) {
                $articleData['emplacement_id'] = $emplacementRetourId;
            }
            $article->update($articleData);
        });
    }

    /**
     * Historique des attributions d'un article, de la plus récente à la plus ancienne.
     *
     * @return Collection<int, ArticleAttribution>
     */
    public static function getAttributionsPourArticle(int $articleId): Collection
    {
        return self::historique()->where('article_id', $articleId)->get();
    }

    /**
     * Historique des attributions d'un sapeur, de la plus récente à la plus ancienne.
     *
     * @return Collection<int, ArticleAttribution>
     */
    public static function getAttributionsPourSapeur(int $sapeurId): Collection
    {
        return self::historique()->where('sapeur_id', $sapeurId)->get();
    }

    /**
     * Requête d'historique avec l'article (et son type) et le nom du sapeur.
     *
     * @return Builder<ArticleAttribution>
     */
    private static function historique(): Builder
    {
        return ArticleAttribution::query()
            ->with([
                'article.materielType',
                'sapeur' => fn ($query) => $query->select(['id', DB::raw("CONCAT(nom, ' ', prenom) AS nom_prenom")]),
            ])
            ->orderByDesc('debut')
            ->orderByDesc('id');
    }

    /**
     * @throws ArrayException si la date est postérieure à aujourd'hui
     */
    private static function assertPasDansLeFutur(Carbon $date): void
    {
        if ($date->gt(Carbon::today())) {
            throw new ArrayException([], "La date ne peut pas être dans le futur");
        }
    }
}
