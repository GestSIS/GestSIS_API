<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\InventaireSaisieBusiness;
use App\Models\Article;
use App\Models\Inventaire;
use App\Models\InventaireEmplacement;
use App\Models\InventaireLigne;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class InventaireSaisieTest extends TestCase
{
    use DatabaseTransactions;

    private Inventaire $inventaire;

    private InventaireEmplacement $racine;

    private InventaireEmplacement $enfant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventaire = Inventaire::factory()->create();
        $this->racine = InventaireEmplacement::factory()->create(['inventaire_id' => $this->inventaire->id]);
        $this->enfant = InventaireEmplacement::factory()->enfantDe($this->racine)->create();
    }

    // --- Statut d'une pièce ----------------------------------------------------------------------

    public function testModifierLeStatutEnregistreLeVerificateur(): void
    {
        $ligne = $this->ligne(['numero' => 'LAN-04']);
        $sapeur = Sapeur::factory()->create();

        InventaireSaisieBusiness::modifierStatut($this->inventaire->id, $ligne->id, 'DEFECTUEUX', $sapeur->id);

        $ligne->refresh();
        $this->assertSame('DEFECTUEUX', $ligne->statut);
        $this->assertSame($sapeur->id, $ligne->verifie_par_sapeur_id);
        $this->assertNotNull($ligne->verifie_le);
    }

    public function testRemettreAVerifierEffaceLaVerification(): void
    {
        $ligne = $this->ligne(['numero' => 'LAN-04', 'statut' => 'PRESENT', 'verifie_le' => now()]);

        $this->json('PUT', $this->url("lignes/{$ligne->id}"), ['statut' => null], $this->headers())->assertStatus(200);

        $ligne->refresh();
        $this->assertNull($ligne->statut);
        $this->assertNull($ligne->verifie_le);
    }

    public function testLaRemarqueSeModifieSansToucherAuStatut(): void
    {
        $ligne = $this->ligne(['numero' => 'LAN-04', 'statut' => 'ABSENT']);

        $this->json('PUT', $this->url("lignes/{$ligne->id}"), ['remarque_sapeur' => 'Prêtée au SIS voisin'], $this->headers())
            ->assertStatus(200)
            ->assertJsonPath('data.id', $this->inventaire->id);

        $ligne->refresh();
        $this->assertSame('ABSENT', $ligne->statut);
        $this->assertSame('Prêtée au SIS voisin', $ligne->remarque_sapeur);
    }

    public function testUnePieceEnPlusNePeutPasEtreMarqueeManquante(): void
    {
        $ligne = InventaireLigne::factory()->for($this->racine, 'inventaireEmplacement')->enPlus()->create(['numero' => 'X-1']);

        $this->json('PUT', $this->url("lignes/{$ligne->id}"), ['statut' => 'ABSENT'], $this->headers())->assertStatus(422);
        $this->assertSame('PRESENT', $ligne->refresh()->statut);
    }

    public function testUnInventaireValideNePeutPlusEtreModifie(): void
    {
        $ligne = $this->ligne(['numero' => 'LAN-04']);
        $this->inventaire->update(['statut' => 'VALIDE']);

        $this->json('PUT', $this->url("lignes/{$ligne->id}"), ['statut' => 'PRESENT'], $this->headers())->assertStatus(422);
        $this->assertNull($ligne->refresh()->statut);
    }

    public function testUneLigneDUnAutreInventaireRenvoie404(): void
    {
        $autre = InventaireLigne::factory()->create();

        $this->json('PUT', $this->url("lignes/{$autre->id}"), ['statut' => 'PRESENT'], $this->headers())->assertStatus(404);
    }

    // --- Lots --------------------------------------------------------------------------------------

    public function testMoinsQuePrevuMarqueManquantesLesPiecesLesPlusAnciennes(): void
    {
        $type = MaterielType::factory()->create();
        $ancienne = $this->piece($type, null);
        $recente = $this->piece($type, null);

        $this->json('PUT', $this->url("lots/{$recente->id}"), ['quantite' => 1], $this->headers())->assertStatus(200);

        $this->assertSame('ABSENT', $ancienne->refresh()->statut);
        $this->assertSame('PRESENT', $recente->refresh()->statut);
    }

    public function testDesPeremptionsDifferentesSeComptentAPart(): void
    {
        $type = MaterielType::factory()->create();
        $mars = $this->piece($type, '2027-03-01');
        $juin = $this->piece($type, '2027-06-01');

        $this->json('PUT', $this->url("lots/{$mars->id}"), ['quantite' => 0], $this->headers())->assertStatus(200);

        $this->assertSame('ABSENT', $mars->refresh()->statut);
        $this->assertNull($juin->refresh()->statut);
    }

    public function testLesDefectueusesSontPriseParmiLesPiecesConstateesEtGardeesAuRecomptage(): void
    {
        $type = MaterielType::factory()->create();
        $pieces = collect(range(1, 4))->map(fn () => $this->piece($type, null));

        $this->json('PUT', $this->url("lots/{$pieces[0]->id}"), ['quantite' => 3, 'defectueux' => 1], $this->headers())->assertStatus(200);
        $this->assertSame(['ABSENT', 'DEFECTUEUX', 'PRESENT', 'PRESENT'], $pieces->map(fn ($p) => $p->refresh()->statut)->all());

        $this->json('PUT', $this->url("lots/{$pieces[0]->id}"), ['quantite' => 4], $this->headers())->assertStatus(200);
        $this->assertSame(['DEFECTUEUX', 'PRESENT', 'PRESENT', 'PRESENT'], $pieces->map(fn ($p) => $p->refresh()->statut)->all());
    }

    public function testPlusDeDefectueusesQueDePiecesPrevuesConstateesEstRefuse(): void
    {
        $type = MaterielType::factory()->create();
        $piece = $this->piece($type, null);

        $this->json('PUT', $this->url("lots/{$piece->id}"), ['quantite' => 3, 'defectueux' => 2], $this->headers())->assertStatus(422);
        $this->assertNull($piece->refresh()->statut);
    }

    public function testLaPrecisionDUnLotVautPourToutesSesPiecesMemeLeSurplus(): void
    {
        $type = MaterielType::factory()->create();
        $piece = $this->piece($type, null);
        $autre = $this->piece($type, null);

        $this->json('PUT', $this->url("lignes/{$piece->id}"), ['remarque_sapeur' => 'Emballage déchiré'], $this->headers())->assertStatus(200);
        $this->json('PUT', $this->url("lots/{$piece->id}"), ['quantite' => 3], $this->headers())->assertStatus(200);

        $this->assertSame('Emballage déchiré', $autre->refresh()->remarque_sapeur);
        $this->assertSame('Emballage déchiré', $this->enPlusDuType($type)->sole()->remarque_sapeur);
    }

    public function testPlusQuePrevuCreeLeSurplusEnPlusPuisLeRetireSiOnCorrige(): void
    {
        $type = MaterielType::factory()->create();
        $piece = $this->piece($type, null);

        $this->json('PUT', $this->url("lots/{$piece->id}"), ['quantite' => 3], $this->headers())->assertStatus(200);
        $this->assertSame('PRESENT', $piece->refresh()->statut);
        $this->assertSame(2, $this->enPlusDuType($type)->count());
        $this->assertSame($piece->designation, $this->enPlusDuType($type)->first()->designation);

        $this->json('PUT', $this->url("lots/{$piece->id}"), ['quantite' => 2], $this->headers())->assertStatus(200);
        $this->assertSame(1, $this->enPlusDuType($type)->count());
    }

    public function testUneQuantiteVideRemetLeLotAVerifier(): void
    {
        $type = MaterielType::factory()->create();
        $piece = $this->piece($type, null);
        $this->json('PUT', $this->url("lots/{$piece->id}"), ['quantite' => 2], $this->headers())->assertStatus(200);

        $this->json('PUT', $this->url("lots/{$piece->id}"), ['quantite' => null], $this->headers())->assertStatus(200);

        $this->assertNull($piece->refresh()->statut);
        $this->assertSame(0, $this->enPlusDuType($type)->count());
    }

    public function testUnePieceNumeroteeNeSeComptePasParLot(): void
    {
        $ligne = $this->ligne(['numero' => 'LAN-04']);

        $this->json('PUT', $this->url("lots/{$ligne->id}"), ['quantite' => 1], $this->headers())->assertStatus(422);
    }

    // --- Matériel en plus --------------------------------------------------------------------------

    public function testUnNumeroDUnArticleExistantYEstRattache(): void
    {
        $type = MaterielType::factory()->numerote()->create(['designation' => 'Lampe frontale']);
        $article = Article::factory()->create(['numero' => 'LAM-021', 'materiel_type_id' => $type->id]);

        $this->ajouter(['numero' => 'LAM-021'])->assertStatus(201);

        $ligne = InventaireLigne::where('numero', 'LAM-021')->where('inventaire_emplacement_id', $this->enfant->id)->sole();
        $this->assertSame('EN_PLUS', $ligne->origine);
        $this->assertSame('PRESENT', $ligne->statut);
        $this->assertSame($article->id, $ligne->article_id);
        $this->assertSame($type->id, $ligne->materiel_type_id);
        $this->assertSame('Lampe frontale', $ligne->designation);
    }

    public function testLeMaterielEnPlusGardeCeQueLeSapeurAReleve(): void
    {
        $type = MaterielType::factory()->create(['designation' => 'Pansement compressif']);
        $this->enfant->emplacement->update(['est_compartimentable' => true]);

        $this->ajouter([
            'materiel_type_id' => $type->id,
            'quantite' => 2,
            'compartiment' => ' Tiroir haut ',
            'date_peremption' => '2027-03-31',
            'remarque_sapeur' => 'Sous le siège passager',
        ])->assertStatus(201);

        $lignes = $this->enPlusDuType($type)->get();
        $this->assertCount(2, $lignes);
        foreach ($lignes as $ligne) {
            $this->assertSame('Tiroir haut', $ligne->compartiment);
            $this->assertSame('2027-03-31', $ligne->date_peremption->toDateString());
            $this->assertSame('Sous le siège passager', $ligne->remarque_sapeur);
        }
    }

    public function testLeCompartimentEstIgnoreHorsEmplacementCompartimente(): void
    {
        $type = MaterielType::factory()->create();

        $this->ajouter(['materiel_type_id' => $type->id, 'compartiment' => 'Tiroir haut'])->assertStatus(201);

        $this->assertSame('', $this->enPlusDuType($type)->sole()->compartiment);
    }

    public function testUnVehiculeNePeutPasEtreAjouteEnPlus(): void
    {
        $vehicule = MaterielType::factory()->create(['est_emplacement' => true]);

        $this->ajouter(['materiel_type_id' => $vehicule->id])->assertStatus(422);
    }

    public function testUnNumeroDejaPrevuDansLInventaireEstRefuse(): void
    {
        $this->ligne(['numero' => 'LAN-04']);

        $this->ajouter(['numero' => 'LAN-04'])->assertStatus(422);
    }

    public function testUnNumeroInconnuSansTypeEstRefuse(): void
    {
        $this->ajouter(['numero' => 'INCONNU-1'])->assertStatus(422);
    }

    public function testUnNouveauTypeEstGardeCommeDesignationSansType(): void
    {
        $this->ajouter(['numero' => 'INCONNU-1', 'nouveau_type' => 'Seau pliable'])->assertStatus(201);

        $ligne = InventaireLigne::where('numero', 'INCONNU-1')->sole();
        $this->assertNull($ligne->materiel_type_id);
        $this->assertSame('Seau pliable', $ligne->designation);
    }

    public function testDuMaterielNonNumeroteCreeLaQuantiteDemandee(): void
    {
        $type = MaterielType::factory()->create();

        $this->ajouter(['materiel_type_id' => $type->id, 'quantite' => 3])->assertStatus(201);

        $this->assertSame(3, $this->enPlusDuType($type)->count());
    }

    public function testDuMaterielTrouveDUneAutrePeremptionFormeSonPropreLot(): void
    {
        $type = MaterielType::factory()->create(['designation' => 'Pansement']);
        $prevue = $this->piece($type, '2027-03-01', ['designation' => 'Pansement']);

        $this->ajouter(['materiel_type_id' => $type->id, 'quantite' => 2, 'date_peremption' => '2027-06-01'])->assertStatus(201);

        $this->assertNull($prevue->refresh()->statut);
        $this->assertSame(['2027-06-01', '2027-06-01'], $this->enPlusDuType($type)->get()->map(fn ($l) => $l->date_peremption->toDateString())->all());
    }

    public function testLesPrecisionsDeDeuxAjoutsDuMemeLotSeCumulent(): void
    {
        $type = MaterielType::factory()->create();

        $this->ajouter(['materiel_type_id' => $type->id, 'quantite' => 1, 'remarque_sapeur' => 'Sous le siège'])->assertStatus(201);
        $this->ajouter(['materiel_type_id' => $type->id, 'quantite' => 1, 'remarque_sapeur' => 'Emballage ouvert'])->assertStatus(201);

        $this->assertSame(['Sous le siège · Emballage ouvert'], $this->enPlusDuType($type)->pluck('remarque_sapeur')->unique()->values()->all());
    }

    public function testDuMaterielNonNumeroteDUnLotPrevuAugmenteSaQuantite(): void
    {
        $type = MaterielType::factory()->create(['designation' => 'Raccord Storz 55']);
        $prevue = $this->piece($type, null, ['designation' => 'Raccord Storz 55']);

        $this->ajouter(['materiel_type_id' => $type->id, 'quantite' => 2])->assertStatus(201);

        $this->assertSame('PRESENT', $prevue->refresh()->statut);
        $this->assertSame(2, $this->enPlusDuType($type)->count());
    }

    public function testRetirerSupprimeUneLigneEnPlusMaisPasUnePiecePrevue(): void
    {
        $enPlus = InventaireLigne::factory()->for($this->racine, 'inventaireEmplacement')->enPlus()->create();
        $prevue = $this->ligne(['numero' => 'LAN-04']);

        $this->json('DELETE', $this->url("lignes/{$enPlus->id}"), [], $this->headers())->assertStatus(200);
        $this->json('DELETE', $this->url("lignes/{$prevue->id}"), [], $this->headers())->assertStatus(422);

        $this->assertModelMissing($enPlus);
        $this->assertModelExists($prevue);
    }

    // --- Reste conforme ----------------------------------------------------------------------------

    public function testResteConformeMarquePresentesLesPiecesAVerifierDuNoeudSeulement(): void
    {
        $aVerifier = $this->ligne(['numero' => 'A-1'], $this->racine);
        $manquante = $this->ligne(['numero' => 'A-2', 'statut' => 'ABSENT'], $this->racine);
        $dansLEnfant = $this->ligne(['numero' => 'A-3']);

        $this->json('POST', $this->url("emplacements/{$this->racine->id}/conformes"), [], $this->headers())->assertStatus(200);

        $this->assertSame('PRESENT', $aVerifier->refresh()->statut);
        $this->assertSame('ABSENT', $manquante->refresh()->statut);
        $this->assertNull($dansLEnfant->refresh()->statut);
    }

    // --- Recherche par numéro ----------------------------------------------------------------------

    public function testArticleParNumeroDecritLArticleEnService(): void
    {
        $type = MaterielType::factory()->numerote()->create(['designation' => 'Lampe frontale']);
        $article = Article::factory()->create(['numero' => 'LAM-021', 'materiel_type_id' => $type->id, 'designation' => '', 'sapeur_id' => null]);

        $this->json('GET', $this->url('article-par-numero'), ['numero' => ' LAM-021 '], $this->headers())
            ->assertStatus(200)
            ->assertJsonPath('data.id', $article->id)
            ->assertJsonPath('data.designation', 'Lampe frontale')
            ->assertJsonPath('data.emplacement_id', $article->emplacement_id)
            ->assertJsonPath('data.attribue', false);
    }

    public function testArticleParNumeroIgnoreLesArticlesSupprimes(): void
    {
        Article::factory()->supprime()->create(['numero' => 'LAM-099']);

        $this->json('GET', $this->url('article-par-numero'), ['numero' => 'LAM-099'], $this->headers())
            ->assertStatus(200)
            ->assertJsonPath('data', null);
    }

    // --- Outils ------------------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $attributes
     */
    private function ligne(array $attributes, ?InventaireEmplacement $noeud = null): InventaireLigne
    {
        return InventaireLigne::factory()->for($noeud ?? $this->enfant, 'inventaireEmplacement')->create($attributes);
    }

    /**
     * Pièce prévue d'un lot non numéroté du nœud enfant.
     *
     * @param array<string, mixed> $attributes
     */
    private function piece(MaterielType $type, ?string $peremption, array $attributes = []): InventaireLigne
    {
        $article = Article::factory()->create(['materiel_type_id' => $type->id, 'date_peremption' => $peremption]);

        return $this->ligne([
            'article_id' => $article->id,
            'materiel_type_id' => $type->id,
            'designation' => 'Couverture de survie',
            'date_peremption' => $peremption,
            ...$attributes,
        ]);
    }

    private function enPlusDuType(MaterielType $type): Builder
    {
        return InventaireLigne::where('materiel_type_id', $type->id)->where('origine', 'EN_PLUS');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function ajouter(array $data): TestResponse
    {
        return $this->json('POST', $this->url('lignes'), ['inventaire_emplacement_id' => $this->enfant->id, ...$data], $this->headers());
    }

    private function url(string $path): string
    {
        return "/api/v2/inventaires/{$this->inventaire->id}/{$path}";
    }

    /**
     * @return array<string, mixed>
     */
    private function headers(): array
    {
        return ['Sis-Key' => 1];
    }
}
