<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\InventaireBusiness;
use App\Models\Article;
use App\Models\Emplacement;
use App\Models\Inventaire;
use App\Models\InventaireEmplacement;
use App\Models\InventaireLigne;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Database\Factories\InventaireLigneFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class InventaireEcartTest extends TestCase
{
    use DatabaseTransactions;

    private Inventaire $inventaire;

    private InventaireEmplacement $noeud;

    private MaterielType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventaire = Inventaire::factory()->create(['statut' => 'VALIDE']);
        $this->noeud = InventaireEmplacement::factory()->create(['inventaire_id' => $this->inventaire->id]);
        $this->noeud->emplacement->update(['est_compartimentable' => true]);
        $this->type = MaterielType::factory()->create(['statut' => true, 'designation' => 'Couverture de survie']);
    }

    // --- Pièce numérotée ---------------------------------------------------------------------------

    public function testUnArticleManquantEncoreEnStockSeProposeASupprimer(): void
    {
        $ligne = $this->prevue(['numero' => 'LAM-04'], 'ABSENT');

        $ecart = $this->ecart("piece-{$ligne->id}");
        $this->assertNull($ecart['resolution']);
        $this->assertSame('SUPPRIMER', $ecart['proposition']);

        $this->traiter($ecart, 'SORTIR_DU_STOCK')->assertStatus(200);
        $this->assertNotNull($ligne->article->refresh()->supprime_le);
        $this->assertSame('SORTIR_DU_STOCK', $ligne->refresh()->decision);
    }

    public function testUnArticleManquantAttribueDepuisNEstPlusConcerneEtNeSeSupprimePas(): void
    {
        $ligne = $this->prevue(['numero' => 'LAM-04'], 'ABSENT');
        $ligne->article->update(['emplacement_id' => null, 'sapeur_id' => Sapeur::factory()->create()->id]);

        $ecart = $this->ecart("piece-{$ligne->id}");
        $this->assertSame('PLUS_CONCERNE', $ecart['resolution']);
        $this->assertSame(['AUCUNE_ACTION'], $ecart['decisions']);

        $this->traiter($ecart, 'SORTIR_DU_STOCK')->assertStatus(422);
        $this->traiter($ecart, 'AUCUNE_ACTION')->assertStatus(200);
        $this->assertNull($ligne->article->refresh()->supprime_le);
        $this->assertStringStartsWith('Plus concerné', $ligne->refresh()->remarque_traitement);
    }

    public function testUnArticleManquantDejaSupprimeEstAJour(): void
    {
        $ligne = $this->prevue(['numero' => 'LAM-04'], 'ABSENT');
        $ligne->article->update(['supprime_le' => '2026-11-15']);

        $this->assertSame('A_JOUR', $this->ecart("piece-{$ligne->id}")['resolution']);
    }

    public function testUnArticleTrouveIciAilleursEnStockSeDeplaceEtSonAttributionSeClot(): void
    {
        $type = MaterielType::factory()->numerote()->create(['statut' => true, 'est_attribuable' => true]);
        $article = Article::factory()->create(['numero' => 'LAM-021', 'materiel_type_id' => $type->id, 'sapeur_id' => Sapeur::factory()]);
        $ligne = $this->ligne()->enPlus()->create(['article_id' => $article->id, 'numero' => 'LAM-021', 'materiel_type_id' => $type->id, 'compartiment' => 'G1']);

        $ecart = $this->ecart("piece-{$ligne->id}");
        $this->assertSame('DEPLACER', $ecart['proposition']);
        $this->traiter($ecart, 'AJOUTER_AU_STOCK')->assertStatus(200);

        $article->refresh();
        $this->assertNull($article->sapeur_id);
        $this->assertSame($this->noeud->emplacement_id, $article->emplacement_id);
        $this->assertSame('G1', $article->compartiment);
    }

    public function testUnArticleTrouveIciDejaRangeIciEstAJour(): void
    {
        $article = Article::factory()->create(['numero' => 'LAM-021', 'emplacement_id' => $this->noeud->emplacement_id]);
        $ligne = $this->ligne()->enPlus()->create(['article_id' => $article->id, 'numero' => 'LAM-021']);

        $this->assertSame('A_JOUR', $this->ecart("piece-{$ligne->id}")['resolution']);
    }

    public function testUnNouvelArticleSeCreeAvecLeReleveDuSapeurEtSaConfiguration(): void
    {
        $type = MaterielType::factory()->numerote()->create(['statut' => true, 'est_taillee' => true]);
        $ligne = $this->ligne()->enPlus()->create([
            'numero' => 'VES-150', 'designation' => 'Veste', 'compartiment' => 'Armoire 3', 'taille' => 'M', 'est_etiquete' => true,
        ]);
        $ecart = $this->ecart("piece-{$ligne->id}");
        $this->assertSame('CREER', $ecart['proposition']);

        $this->traiter($ecart, 'AJOUTER_AU_STOCK')
            ->assertStatus(422)
            ->assertJsonFragment(['message' => "Choisissez le type de « Veste » avant de l'ajouter au stock"]);
        $this->traiter($ecart, 'AJOUTER_AU_STOCK', ['materiel_type_id' => $type->id, 'article' => ['achat' => '2024', 'remarque' => 'Trouvé au toit']])
            ->assertStatus(200);

        $article = $ligne->refresh()->article;
        $this->assertSame('VES-150', $article->numero);
        $this->assertSame($this->noeud->emplacement_id, $article->emplacement_id);
        $this->assertSame('Armoire 3', $article->compartiment);
        $this->assertSame('M', $article->taille);
        $this->assertSame('2024', $article->achat);
        $this->assertSame('Trouvé au toit', $article->remarque);
        $this->assertSame($type->id, $article->materiel_type_id);
        // La ligne garde ce que le sapeur a relevé : un type encore à créer
        $this->assertNull($ligne->materiel_type_id);
    }

    public function testUnArticleTrouveIciDansUnEmplacementSupprimeDepuisNEstPlusConcerne(): void
    {
        $article = Article::factory()->create(['numero' => 'LAM-021']);
        $ligne = $this->ligne()->enPlus()->create(['article_id' => $article->id, 'numero' => 'LAM-021']);
        $this->noeud->update(['emplacement_id' => null]);

        $ecart = $this->ecart("piece-{$ligne->id}");
        $this->assertSame('PLUS_CONCERNE', $ecart['resolution']);
        $this->traiter($ecart, 'AUCUNE_ACTION')->assertStatus(200);
    }

    public function testUnTypeVehiculeNePeutPasEtreAjouteAuStock(): void
    {
        $vehicule = MaterielType::factory()->create(['statut' => true, 'est_emplacement' => true]);
        $ligne = $this->ligne()->enPlus()->create(['numero' => 'VEH-1']);

        $this->traiter($this->ecart("piece-{$ligne->id}"), 'AJOUTER_AU_STOCK', ['materiel_type_id' => $vehicule->id])->assertStatus(422);
    }

    // --- Lots --------------------------------------------------------------------------------------

    public function testUnLotManquantRetireDuStockLesPiecesReleveesManquantes(): void
    {
        $presente = $this->prevue([], 'PRESENT');
        $absentes = collect([$this->prevue([], 'ABSENT'), $this->prevue([], 'ABSENT')]);

        $ecart = $this->ecart("lot-{$presente->id}-quantite");
        $this->assertSame(['RETIRER', 2, 3, 1], [$ecart['proposition'], $ecart['quantite'], $ecart['actuel'], $ecart['constate']]);

        $this->traiter($ecart, 'SORTIR_DU_STOCK')->assertStatus(200);
        $this->assertNull($presente->article->refresh()->supprime_le);
        $absentes->each(fn (InventaireLigne $l) => $this->assertNotNull($l->article->refresh()->supprime_le));
    }

    public function testUnLotModifieDepuisLInventaireGardeSesPieces(): void
    {
        $presente = $this->prevue([], 'PRESENT');
        $absente = $this->prevue([], 'ABSENT');
        // Compartiment réorganisé après le démarrage : la clé du lot ne correspond plus
        Article::whereIn('id', [$presente->article_id, $absente->article_id])->update(['compartiment' => 'Nouveau']);

        $ecart = $this->ecart("lot-{$presente->id}-quantite");
        $this->assertSame(['RETIRER', 1, 2], [$ecart['proposition'], $ecart['quantite'], $ecart['actuel']]);

        $this->traiter($ecart, 'SORTIR_DU_STOCK')->assertStatus(200);
        $this->assertNotNull($absente->article->refresh()->supprime_le);
        $this->assertNull($presente->article->refresh()->supprime_le);
    }

    public function testUnePieceAjouteeDepuisLInventaireNEstJamaisRetiree(): void
    {
        $presente = $this->prevue([], 'PRESENT');
        $absente = $this->prevue([], 'ABSENT');
        $ajoutee = Article::factory()->create($this->articleDuLot());

        $ecart = $this->ecart("lot-{$presente->id}-quantite");
        $this->assertSame(['RETIRER', 1, 3, 1, 0], [$ecart['proposition'], $ecart['quantite'], $ecart['actuel'], $ecart['ajoutes_depuis'], $ecart['partis_depuis']]);

        $this->traiter($ecart, 'SORTIR_DU_STOCK')->assertStatus(200);
        $this->assertNotNull($absente->article->refresh()->supprime_le);
        $this->assertNull($ajoutee->refresh()->supprime_le);
    }

    public function testDuMaterielEnPlusDejaAjouteAuStockDepuisEstAJourSansDoublon(): void
    {
        $prevue = $this->prevue([], 'PRESENT');
        $this->enPlusDuLot($prevue, 2);
        // Ajoutées au stock par quelqu'un d'autre après l'inventaire
        Article::factory()->count(2)->create($this->articleDuLot());

        $ecart = $this->ecart("lot-{$prevue->id}-quantite");
        $this->assertSame('A_JOUR', $ecart['resolution']);

        $this->traiter($ecart, 'AUCUNE_ACTION')->assertStatus(200);
        $this->assertSame(3, Article::nonSupprimes()->where('materiel_type_id', $this->type->id)->count());
    }

    public function testDuMaterielEnPlusSAjouteAuStockPieceParPiece(): void
    {
        $prevue = $this->prevue([], 'PRESENT');
        $enPlus = $this->enPlusDuLot($prevue, 2);

        $ecart = $this->ecart("lot-{$prevue->id}-quantite");
        $this->assertSame(['AJOUTER', 2], [$ecart['proposition'], $ecart['quantite']]);

        $this->traiter($ecart, 'AJOUTER_AU_STOCK', [
            'article' => ['achat' => '2025'],
            'articles' => [['remarque' => 'Première'], ['remarque' => 'Seconde']],
        ])->assertStatus(200);

        $this->assertSame(['Première', 'Seconde'], $enPlus->map(fn (InventaireLigne $l) => $l->refresh()->article->remarque)->all());
        $this->assertSame('2025', $enPlus[1]->article->achat);
        $this->assertSame(3, Article::nonSupprimes()->where('materiel_type_id', $this->type->id)->count());
    }

    public function testUnLotNonPrevuAvecLeTypeEtLEmplacementChoisisParLeResponsable(): void
    {
        $lignes = $this->ligne()->enPlus()->count(2)->create(['designation' => 'Seau pliable', 'compartiment' => 'Toit']);
        $ailleurs = Emplacement::factory()->create(['est_compartimentable' => true]);

        $this->traiter($this->ecart("lot-{$lignes[0]->id}-quantite"), 'AJOUTER_AU_STOCK', [
            'materiel_type_id' => $this->type->id,
            'article' => ['emplacement_id' => $ailleurs->id],
        ])->assertStatus(200);

        foreach ($lignes as $ligne) {
            $article = $ligne->refresh()->article;
            $this->assertSame($ailleurs->id, $article->emplacement_id);
            $this->assertSame('Toit', $article->compartiment);
        }
        $this->assertNotSame($lignes[0]->article_id, $lignes[1]->article_id);
    }

    public function testDeuxPiecesNumeroteesAvecLeMemeNumeroSontRefusees(): void
    {
        $type = MaterielType::factory()->numerote()->create(['statut' => true]);
        $lignes = $this->ligne()->enPlus()->count(2)->create(['materiel_type_id' => $type->id, 'designation' => 'Seau']);

        $this->traiter($this->ecart("lot-{$lignes[0]->id}-quantite"), 'AJOUTER_AU_STOCK', ['article' => ['numero' => 'SEAU-01']])
            ->assertStatus(422);
        $this->assertSame(0, Article::where('numero', 'SEAU-01')->count());
    }

    public function testLesDefectueusesSeRetirentAPartEtLaQuantiteNeLesRetirePasUneSecondeFois(): void
    {
        $presente = $this->prevue([], 'PRESENT');
        $defectueuse = $this->prevue([], 'DEFECTUEUX');
        $absente = $this->prevue([], 'ABSENT');

        $this->traiter($this->ecart("lot-{$presente->id}-def"), 'SORTIR_DU_STOCK')->assertStatus(200);
        $this->assertNotNull($defectueuse->article->refresh()->supprime_le);

        $quantite = $this->ecart("lot-{$presente->id}-quantite");
        $this->assertSame(['RETIRER', 1], [$quantite['proposition'], $quantite['quantite']]);
        $this->traiter($quantite, 'SORTIR_DU_STOCK')->assertStatus(200);
        $this->assertNotNull($absente->article->refresh()->supprime_le);
        $this->assertNull($presente->article->refresh()->supprime_le);
    }

    // --- Garde-fous --------------------------------------------------------------------------------

    public function testUnStockChangeDepuisLAffichageEstRefuseSansRienAppliquer(): void
    {
        $presente = $this->prevue([], 'PRESENT');
        $absente = $this->prevue([], 'ABSENT');
        $affiche = $this->ecart("lot-{$presente->id}-quantite");
        $absente->article->update(['emplacement_id' => null, 'sapeur_id' => Sapeur::factory()->create()->id]);

        $this->traiter($affiche, 'SORTIR_DU_STOCK')
            ->assertStatus(422)
            ->assertJsonFragment(['message' => "Le stock a changé depuis l'affichage : vérifiez la proposition mise à jour avant de l'appliquer"]);
        $this->assertNull($presente->article->refresh()->supprime_le);
        $this->assertNull($absente->refresh()->decision);
    }

    public function testUnEcartDejaTraiteOuUnInventaireEnCoursEstRefuse(): void
    {
        $ligne = $this->prevue(['numero' => 'LAM-04'], 'ABSENT');
        $ecart = $this->ecart("piece-{$ligne->id}");
        $this->traiter($ecart, 'AUCUNE_ACTION')->assertStatus(200);
        $this->traiter($ecart, 'AUCUNE_ACTION')->assertStatus(422)->assertJsonFragment(['message' => 'Cet écart a déjà été traité']);

        $this->inventaire->update(['statut' => 'EN_COURS']);
        $this->json('GET', $this->url('ecarts'), [], ['Sis-Key' => 1])->assertStatus(200)->assertJsonPath('data', []);
        $this->traiter($ecart, 'AUCUNE_ACTION')->assertStatus(422);
    }

    public function testUnePiecePresenteNEstPasUnEcart(): void
    {
        $ligne = $this->prevue(['numero' => 'LAM-04'], 'PRESENT');

        $this->assertNull(collect($this->ecarts())->firstWhere('id', "piece-{$ligne->id}"));
    }

    // --- Outils ------------------------------------------------------------------------------------

    private function ligne(): InventaireLigneFactory
    {
        return InventaireLigne::factory()->for($this->noeud, 'inventaireEmplacement');
    }

    /**
     * @return array<string, mixed>
     */
    private function articleDuLot(): array
    {
        return ['materiel_type_id' => $this->type->id, 'emplacement_id' => $this->noeud->emplacement_id, 'numero' => '', 'designation' => '', 'compartiment' => '', 'taille' => '', 'date_peremption' => null, 'sapeur_id' => null];
    }

    /**
     * Pièce prévue au démarrage, en stock dans l'emplacement, avec son statut constaté.
     *
     * @param array<string, mixed> $article
     */
    private function prevue(array $article, string $statut): InventaireLigne
    {
        $stock = Article::factory()->create([...$this->articleDuLot(), ...$article]);

        return InventaireLigne::create([
            ...InventaireBusiness::ligneAttendue($stock->load('materielType'), $this->noeud->id),
            'origine' => 'ATTENDU',
            'statut' => $statut,
        ]);
    }

    /**
     * @return Collection<int, InventaireLigne>
     */
    private function enPlusDuLot(InventaireLigne $prevue, int $nombre): Collection
    {
        return collect(range(1, $nombre))->map(fn () => $this->ligne()->enPlus()->create(
            $prevue->only(['materiel_type_id', 'designation', 'compartiment', 'taille', 'date_peremption']),
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function ecarts(): array
    {
        return $this->json('GET', $this->url('ecarts'), [], ['Sis-Key' => 1])->assertStatus(200)->json('data');
    }

    /**
     * @return array<string, mixed>
     */
    private function ecart(string $id): array
    {
        $ecart = collect($this->ecarts())->firstWhere('id', $id);
        $this->assertNotNull($ecart, "Écart {$id} introuvable");

        return $ecart;
    }

    /**
     * @param array<string, mixed> $ecart
     * @param array<string, mixed> $data
     */
    private function traiter(array $ecart, string $decision, array $data = []): TestResponse
    {
        return $this->json('POST', $this->url('ecarts/traiter'), [
            'ecart' => $ecart['id'],
            'signature' => $ecart['signature'],
            'decision' => $decision,
            ...$data,
        ], ['Sis-Key' => 1]);
    }

    private function url(string $path): string
    {
        return "/api/v2/inventaires/{$this->inventaire->id}/{$path}";
    }
}
