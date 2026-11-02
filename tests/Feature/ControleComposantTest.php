<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\ControleExecBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleExec;
use App\Models\ControleMaterielType;
use App\Models\ControleTache;
use App\Models\ControleTacheMaterielType;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Une tâche COMPOSANT a pour résultat un article scanné (ex : la pulmocommande
 * et le masque utilisés lors du contrôle d'un cacolet).
 */
class ControleComposantTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @param array<int, array<string, mixed>> $taches
     * @return array<string, mixed>
     */
    private function payloadControle(array $taches): array
    {
        return [
            'nom' => 'Contrôle APR',
            'recurrence_type' => 'PERIODIQUE',
            'recurrence_value' => 12,
            'taches' => $taches,
            'materiel_type_ids' => [],
        ];
    }

    /**
     * @param int[] $typeIds
     * @return array<string, mixed>
     */
    private function tacheComposant(array $typeIds, array $extra = []): array
    {
        return [
            'order' => 1,
            'nom' => 'Pulmo',
            'type' => 'COMPOSANT',
            'obligatoire' => true,
            'materiel_type_ids' => $typeIds,
            ...$extra,
        ];
    }

    public function testCreerControleAvecComposantEnregistreLesTypesAcceptes(): void
    {
        $a = MaterielType::factory()->numerote()->create();
        $b = MaterielType::factory()->numerote()->create();

        $response = $this->json('POST', '/api/v2/controles', $this->payloadControle([
            $this->tacheComposant([$a->id, $b->id], ['obligatoire' => false]),
        ]), ['Sis-Key' => 1]);

        $response->assertStatus(201);
        $this->assertFalse($response->json('data.taches.0.obligatoire'));
        $this->assertCount(2, $response->json('data.taches.0.materiel_types'));
    }

    public function testModifierComposantRemplaceLesTypesAcceptes(): void
    {
        $a = MaterielType::factory()->numerote()->create();
        $b = MaterielType::factory()->numerote()->create();
        $id = $this->json('POST', '/api/v2/controles', $this->payloadControle([
            $this->tacheComposant([$a->id]),
        ]), ['Sis-Key' => 1])->json('data.id');
        $tache = ControleTache::where('controle_id', $id)->first();

        $this->json('PUT', "/api/v2/controles/{$id}", $this->payloadControle([
            $this->tacheComposant([$b->id], ['id' => $tache->id]),
        ]), ['Sis-Key' => 1])->assertOk();

        $this->assertSame(
            [$b->id],
            ControleTacheMaterielType::where('controle_tache_id', $tache->id)->pluck('materiel_type_id')->all(),
        );
    }

    public function testRouteTachesAccepteUnComposant(): void
    {
        $a = MaterielType::factory()->numerote()->create();
        $b = MaterielType::factory()->numerote()->create();
        $controle = Controle::create(['nom' => 'APR', 'recurrence_type' => 'NON_PERIODIQUE']);

        $response = $this->json('POST', "/api/v2/controles/{$controle->id}/taches", $this->tacheComposant([$a->id]), ['Sis-Key' => 1]);
        $response->assertStatus(201);
        $tacheId = $response->json('data.id');
        $this->assertDatabaseHas('controle_tache_materiel_types', ['controle_tache_id' => $tacheId, 'materiel_type_id' => $a->id]);

        $this->json('PUT', "/api/v2/controles/{$controle->id}/taches/{$tacheId}", $this->tacheComposant([$b->id]), ['Sis-Key' => 1])
            ->assertOk();

        $this->assertSame(
            [$b->id],
            ControleTacheMaterielType::where('controle_tache_id', $tacheId)->pluck('materiel_type_id')->all(),
        );
    }

    public function testPasserComposantABooleanSupprimeLesTypesEtRendObligatoire(): void
    {
        $a = MaterielType::factory()->numerote()->create();
        $id = $this->json('POST', '/api/v2/controles', $this->payloadControle([
            $this->tacheComposant([$a->id], ['obligatoire' => false]),
        ]), ['Sis-Key' => 1])->json('data.id');
        $tache = ControleTache::where('controle_id', $id)->first();

        $this->json('PUT', "/api/v2/controles/{$id}", $this->payloadControle([
            ['id' => $tache->id, 'order' => 1, 'nom' => 'Visuel', 'type' => 'BOOLEAN', 'obligatoire' => false],
        ]), ['Sis-Key' => 1])->assertOk();

        $this->assertTrue($tache->fresh()->obligatoire);
        $this->assertSame(0, ControleTacheMaterielType::where('controle_tache_id', $tache->id)->count());
    }

    public function testBooleanAvecListeDeTypesVideEstAccepte(): void
    {
        $this->json('POST', '/api/v2/controles', $this->payloadControle([
            ['order' => 1, 'nom' => 'Visuel', 'type' => 'BOOLEAN', 'materiel_type_ids' => []],
        ]), ['Sis-Key' => 1])->assertStatus(201);
    }

    public function testTacheNumericPeutEtreFacultative(): void
    {
        $id = $this->json('POST', '/api/v2/controles', $this->payloadControle([
            ['order' => 1, 'nom' => 'Pression', 'type' => 'NUMERIC', 'obligatoire' => false],
        ]), ['Sis-Key' => 1])->json('data.id');

        $this->assertFalse(ControleTache::where('controle_id', $id)->first()->obligatoire);
    }

    public function testTacheBooleanSoumiseFacultativeResteObligatoire(): void
    {
        $id = $this->json('POST', '/api/v2/controles', $this->payloadControle([
            ['order' => 1, 'nom' => 'Visuel', 'type' => 'BOOLEAN', 'obligatoire' => false],
        ]), ['Sis-Key' => 1])->json('data.id');

        $this->assertTrue(ControleTache::where('controle_id', $id)->first()->obligatoire);
    }

    public function testComposantSansTypeEstRefuse(): void
    {
        $this->json('POST', '/api/v2/controles', $this->payloadControle([
            $this->tacheComposant([]),
        ]), ['Sis-Key' => 1])->assertStatus(422)->assertJsonValidationErrors(['taches.0.materiel_type_ids']);
    }

    public function testComposantAvecTypeNonNumeroteEstRefuse(): void
    {
        $type = MaterielType::factory()->create(['est_numerote' => false]);

        $this->json('POST', '/api/v2/controles', $this->payloadControle([
            $this->tacheComposant([$type->id]),
        ]), ['Sis-Key' => 1])->assertStatus(422);

        $this->assertDatabaseMissing('controles', ['nom' => 'Contrôle APR']);
    }

    public function testMemeTypeAccepteParDeuxComposantsEstAccepte(): void
    {
        $type = MaterielType::factory()->numerote()->create();

        $this->json('POST', '/api/v2/controles', $this->payloadControle([
            $this->tacheComposant([$type->id], ['nom' => 'Bouteille 1']),
            $this->tacheComposant([$type->id], ['nom' => 'Bouteille 2', 'order' => 2]),
        ]), ['Sis-Key' => 1])->assertStatus(201);
    }

    /**
     * Contrôle à deux composants (pulmo obligatoire, masque facultatif) et une
     * tâche classique, avec le cacolet à contrôler et un article par composant.
     *
     * @return array{controle: Controle, cacolet: Article, pulmo: Article, masque: Article, tPulmo: ControleTache, tMasque: ControleTache, tVisuel: ControleTache}
     */
    private function scenario(): array
    {
        $controle = Controle::create(['nom' => 'Contrôle APR', 'recurrence_type' => 'NON_PERIODIQUE']);
        $typeCacolet = MaterielType::factory()->numerote()->create();
        ControleMaterielType::create(['controle_id' => $controle->id, 'materiel_type_id' => $typeCacolet->id]);
        $typePulmo = MaterielType::factory()->numerote()->create();
        $typeMasque = MaterielType::factory()->numerote()->create();

        $tPulmo = ControleTache::create(['controle_id' => $controle->id, 'order' => 1, 'nom' => 'Pulmo', 'type' => 'COMPOSANT', 'obligatoire' => true]);
        ControleTacheMaterielType::create(['controle_tache_id' => $tPulmo->id, 'materiel_type_id' => $typePulmo->id]);
        $tMasque = ControleTache::create(['controle_id' => $controle->id, 'order' => 2, 'nom' => 'Masque', 'type' => 'COMPOSANT', 'obligatoire' => false]);
        ControleTacheMaterielType::create(['controle_tache_id' => $tMasque->id, 'materiel_type_id' => $typeMasque->id]);
        $tVisuel = ControleTache::create(['controle_id' => $controle->id, 'order' => 3, 'nom' => 'Visuel', 'type' => 'BOOLEAN']);

        return [
            'controle' => $controle,
            'cacolet' => Article::factory()->create(['materiel_type_id' => $typeCacolet->id, 'statut' => true]),
            'pulmo' => Article::factory()->create(['materiel_type_id' => $typePulmo->id, 'statut' => true]),
            'masque' => Article::factory()->create(['materiel_type_id' => $typeMasque->id, 'statut' => true]),
            'tPulmo' => $tPulmo,
            'tMasque' => $tMasque,
            'tVisuel' => $tVisuel,
        ];
    }

    /**
     * @param array<string, mixed> $s
     * @param array<int, array<string, mixed>> $composants lignes pulmo et masque
     */
    private function creerExec(array $s, array $composants, string $statutVisuel = 'OK'): ControleExec
    {
        return ControleExecBusiness::createExec($s['controle']->id, $s['cacolet']->id, [
            'executed_at' => '2026-01-01',
            'trigger_type' => 'NON_PERIODIQUE',
            'taches' => [...$composants, ['tache_id' => $s['tVisuel']->id, 'statut' => $statutVisuel]],
        ], Sapeur::factory()->create()->id);
    }

    public function testCreateExecEnregistreLeComposantAvecSesChampsFiges(): void
    {
        $s = $this->scenario();

        $exec = $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
            ['tache_id' => $s['tMasque']->id, 'article_id' => $s['masque']->id, 'remarque' => 'texte'],
        ]);

        $ligne = $exec->execTaches->firstWhere('tache_id', $s['tMasque']->id);
        $this->assertSame($s['masque']->id, $ligne->article_id);
        $this->assertSame('Masque', $ligne->nom_snapshot);
        $this->assertSame('COMPOSANT', $ligne->type_snapshot);
        $this->assertFalse($ligne->obligatoire_snapshot);
        $this->assertNull($ligne->statut);
        $this->assertNull($ligne->value_measured);
        $this->assertNull($ligne->value_in_range);
        $this->assertNull($ligne->remarque);
    }

    public function testComposantFacultatifSansCleArticleIdEstEnregistreVide(): void
    {
        $s = $this->scenario();

        $exec = $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
            ['tache_id' => $s['tMasque']->id],
        ]);

        $this->assertNull($exec->execTaches->firstWhere('tache_id', $s['tMasque']->id)->article_id);
    }

    public function testComposantObligatoireVideEstRefuse(): void
    {
        $s = $this->scenario();

        $this->expectException(ArrayException::class);
        $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => null],
            ['tache_id' => $s['tMasque']->id],
        ]);
    }

    public function testArticleInactifEstRefuse(): void
    {
        $s = $this->scenario();
        $s['pulmo']->update(['statut' => false]);

        $this->expectException(ArrayException::class);
        $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
            ['tache_id' => $s['tMasque']->id],
        ]);
    }

    public function testArticleDunTypeNonAccepteEstRefuse(): void
    {
        $s = $this->scenario();

        $this->expectException(ArrayException::class);
        $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['masque']->id],
            ['tache_id' => $s['tMasque']->id],
        ]);
    }

    public function testArticleControleUtiliseCommeComposantEstRefuse(): void
    {
        $s = $this->scenario();
        ControleTacheMaterielType::create(['controle_tache_id' => $s['tPulmo']->id, 'materiel_type_id' => $s['cacolet']->materiel_type_id]);

        $this->expectException(ArrayException::class);
        $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['cacolet']->id],
            ['tache_id' => $s['tMasque']->id],
        ]);
    }

    public function testMemeArticleDansDeuxComposantsEstRefuse(): void
    {
        $s = $this->scenario();
        ControleTacheMaterielType::create(['controle_tache_id' => $s['tMasque']->id, 'materiel_type_id' => $s['pulmo']->materiel_type_id]);

        $this->expectException(ArrayException::class);
        $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
            ['tache_id' => $s['tMasque']->id, 'article_id' => $s['pulmo']->id],
        ]);
    }

    public function testArticleInconnuEstRefuse(): void
    {
        $s = $this->scenario();

        $this->expectException(ArrayException::class);
        $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => 999999],
            ['tache_id' => $s['tMasque']->id],
        ]);
    }

    public function testArticleIdIgnorePourUneTacheBoolean(): void
    {
        $s = $this->scenario();

        $exec = ControleExecBusiness::createExec($s['controle']->id, $s['cacolet']->id, [
            'executed_at' => '2026-01-01',
            'trigger_type' => 'NON_PERIODIQUE',
            'taches' => [
                ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
                ['tache_id' => $s['tMasque']->id],
                ['tache_id' => $s['tVisuel']->id, 'statut' => 'OK', 'article_id' => $s['masque']->id],
            ],
        ], Sapeur::factory()->create()->id);

        $this->assertNull($exec->execTaches->firstWhere('tache_id', $s['tVisuel']->id)->article_id);
    }

    /**
     * @param array<string, mixed> $s
     * @return array<string, mixed>
     */
    private function executionMultiple(array $s, Article $cacolet, Article $pulmo): array
    {
        return [
            'article_id' => $cacolet->id,
            'taches' => [
                ['tache_id' => $s['tPulmo']->id, 'article_id' => $pulmo->id],
                ['tache_id' => $s['tMasque']->id, 'article_id' => null],
                ['tache_id' => $s['tVisuel']->id, 'statut' => 'OK'],
            ],
        ];
    }

    public function testCreateExecsMultipleEnregistreLesComposantsParLigne(): void
    {
        $s = $this->scenario();
        $autreCacolet = Article::factory()->create(['materiel_type_id' => $s['cacolet']->materiel_type_id, 'statut' => true]);
        $autrePulmo = Article::factory()->create(['materiel_type_id' => $s['pulmo']->materiel_type_id, 'statut' => true]);

        $execs = ControleExecBusiness::createExecsMultiple($s['controle']->id, '2026-01-01', 'NON_PERIODIQUE', [
            $this->executionMultiple($s, $s['cacolet'], $s['pulmo']),
            $this->executionMultiple($s, $autreCacolet, $autrePulmo),
        ], Sapeur::factory()->create()->id);

        $this->assertSame($s['pulmo']->id, $execs[0]->execTaches->firstWhere('tache_id', $s['tPulmo']->id)->article_id);
        $this->assertSame($autrePulmo->id, $execs[1]->execTaches->firstWhere('tache_id', $s['tPulmo']->id)->article_id);
    }

    public function testCreateExecsMultipleRefuseUnMemeArticleSurDeuxLignes(): void
    {
        $s = $this->scenario();
        $autreCacolet = Article::factory()->create(['materiel_type_id' => $s['cacolet']->materiel_type_id, 'statut' => true]);

        try {
            ControleExecBusiness::createExecsMultiple($s['controle']->id, '2026-01-01', 'NON_PERIODIQUE', [
                $this->executionMultiple($s, $s['cacolet'], $s['pulmo']),
                $this->executionMultiple($s, $autreCacolet, $s['pulmo']),
            ], Sapeur::factory()->create()->id);
            $this->fail('Le même article sur deux lignes doit être refusé');
        } catch (ArrayException) {
            $this->assertSame(0, ControleExec::where('controle_id', $s['controle']->id)->count());
        }
    }

    public function testComposantVideNInvalidePasUnControleConforme(): void
    {
        $s = $this->scenario();

        $exec = $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
            ['tache_id' => $s['tMasque']->id],
        ]);

        $this->assertTrue($exec->isConforme());
    }

    public function testComposantRenseigneNeMasquePasUneTacheKo(): void
    {
        $s = $this->scenario();

        $exec = $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
            ['tache_id' => $s['tMasque']->id],
        ], 'KO');

        $this->assertFalse($exec->isConforme());
    }

    public function testReferenceNeChangePasLeNombreDexecutionsDeLarticle(): void
    {
        $s = $this->scenario();

        $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
            ['tache_id' => $s['tMasque']->id],
        ]);

        $this->assertSame(0, ControleExec::where('article_id', $s['pulmo']->id)->count());
    }

    /**
     * @param array<string, mixed> $s
     */
    private function execAvecPulmo(array $s): ControleExec
    {
        return $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
            ['tache_id' => $s['tMasque']->id],
        ]);
    }

    /**
     * Résultats à soumettre pour une exécution ; $articles remplace l'article des
     * lignes composant, par id de tâche.
     *
     * @param array<int, ?int|string> $articles
     */
    private function modifierExec(ControleExec $exec, array $articles): ControleExec
    {
        return ControleExecBusiness::updateExec($exec->id, [
            'executed_at' => '2026-02-01',
            'remarque_globale' => null,
            'taches' => $this->lignesModification($exec, $articles),
        ]);
    }

    /**
     * @param array<int, ?int|string> $articles
     * @return array<int, array<string, mixed>>
     */
    private function lignesModification(ControleExec $exec, array $articles): array
    {
        return $exec->execTaches->map(fn ($ligne) => $ligne->type_snapshot === 'COMPOSANT'
            ? ['id' => $ligne->id, 'article_id' => array_key_exists($ligne->tache_id, $articles) ? $articles[$ligne->tache_id] : $ligne->article_id]
            : ['id' => $ligne->id, 'statut' => $ligne->statut])->all();
    }

    public function testUpdateExecChangeLarticleDunComposant(): void
    {
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);
        $autrePulmo = Article::factory()->create(['materiel_type_id' => $s['pulmo']->materiel_type_id, 'statut' => true]);

        $exec = $this->modifierExec($exec, [$s['tPulmo']->id => $autrePulmo->id]);

        $this->assertSame($autrePulmo->id, $exec->execTaches->firstWhere('tache_id', $s['tPulmo']->id)->article_id);
    }

    public function testUpdateExecRemplitPuisVideUneLigneFacultative(): void
    {
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);

        $exec = $this->modifierExec($exec, [$s['tMasque']->id => $s['masque']->id]);
        $this->assertSame($s['masque']->id, $exec->execTaches->firstWhere('tache_id', $s['tMasque']->id)->article_id);

        $exec = $this->modifierExec($exec, [$s['tMasque']->id => null]);
        $this->assertNull($exec->execTaches->firstWhere('tache_id', $s['tMasque']->id)->article_id);
    }

    public function testUpdateExecRefuseDeViderUneLigneObligatoire(): void
    {
        // L'obligation figée à l'exécution fait foi, même si la tâche est devenue facultative.
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);
        $s['tPulmo']->update(['obligatoire' => false]);

        $this->expectException(ArrayException::class);
        $this->modifierExec($exec, [$s['tPulmo']->id => null]);
    }

    public function testUpdateExecConserveUnArticleDevenuInactif(): void
    {
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);
        $s['pulmo']->update(['statut' => false]);

        $exec = $this->modifierExec($exec, []);

        $this->assertSame($s['pulmo']->id, $exec->execTaches->firstWhere('tache_id', $s['tPulmo']->id)->article_id);
    }

    public function testUpdateExecConserveUnArticleDontLeTypeNestPlusAccepte(): void
    {
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);
        $s['tPulmo']->materielTypes()->delete();

        $exec = $this->modifierExec($exec, []);

        $this->assertSame($s['pulmo']->id, $exec->execTaches->firstWhere('tache_id', $s['tPulmo']->id)->article_id);
    }

    public function testUpdateExecArticleEnChaineEstTraiteCommeInchange(): void
    {
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);
        $s['pulmo']->update(['statut' => false]);

        $this->json('PUT', "/api/v2/controle-execs/{$exec->id}", [
            'executed_at' => '2026-02-01',
            'taches' => $this->lignesModification($exec, [$s['tPulmo']->id => (string) $s['pulmo']->id]),
        ], ['Sis-Key' => 1])->assertOk();
    }

    public function testUpdateExecRefuseUnNouvelArticleInactif(): void
    {
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);
        $inactif = Article::factory()->create(['materiel_type_id' => $s['pulmo']->materiel_type_id, 'statut' => false]);

        $this->expectException(ArrayException::class);
        $this->modifierExec($exec, [$s['tPulmo']->id => $inactif->id]);
    }

    public function testUpdateExecApresRenommageConserveLeNomFige(): void
    {
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);
        $s['tPulmo']->update(['nom' => 'Renommée']);

        $exec = $this->modifierExec($exec, []);

        $this->assertSame('Pulmo', $exec->execTaches->firstWhere('tache_id', $s['tPulmo']->id)->nom_snapshot);
    }

    public function testUpdateExecTacheSupprimeeRefuseUnNouvelArticleMaisPermetDeVider(): void
    {
        $s = $this->scenario();
        $exec = $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
            ['tache_id' => $s['tMasque']->id, 'article_id' => $s['masque']->id],
        ]);
        $ligne = $exec->execTaches->firstWhere('tache_id', $s['tMasque']->id);
        $s['tMasque']->delete();
        $exec->refresh()->load('execTaches');
        $autreMasque = Article::factory()->create(['materiel_type_id' => $s['masque']->materiel_type_id, 'statut' => true]);
        $taches = fn (?int $articleId) => $exec->execTaches->map(fn ($l) => match (true) {
            $l->id === $ligne->id => ['id' => $l->id, 'article_id' => $articleId],
            $l->type_snapshot === 'COMPOSANT' => ['id' => $l->id, 'article_id' => $l->article_id],
            default => ['id' => $l->id, 'statut' => $l->statut],
        })->all();

        try {
            ControleExecBusiness::updateExec($exec->id, ['executed_at' => '2026-02-01', 'taches' => $taches($autreMasque->id)]);
            $this->fail('Un nouvel article ne doit pas être accepté');
        } catch (ArrayException) {
            $this->assertSame($s['masque']->id, $ligne->fresh()->article_id);
        }

        ControleExecBusiness::updateExec($exec->id, ['executed_at' => '2026-02-01', 'taches' => $taches(null)]);
        $this->assertNull($ligne->fresh()->article_id);
    }

    public function testUpdateExecTacheRetypeeRefuseUnNouvelArticle(): void
    {
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);
        $autreMasque = $s['masque'];
        ControleTacheMaterielType::create(['controle_tache_id' => $s['tMasque']->id, 'materiel_type_id' => $s['pulmo']->materiel_type_id]);
        $s['tMasque']->update(['type' => 'BOOLEAN']);

        $this->expectException(ArrayException::class);
        $this->modifierExec($exec, [$s['tMasque']->id => $autreMasque->id]);
    }

    public function testUpdateExecRefuseLeMemeArticleSurDeuxLignes(): void
    {
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);
        ControleTacheMaterielType::create(['controle_tache_id' => $s['tMasque']->id, 'materiel_type_id' => $s['pulmo']->materiel_type_id]);

        $this->expectException(ArrayException::class);
        $this->modifierExec($exec, [$s['tMasque']->id => $s['pulmo']->id]);
    }

    public function testUpdateExecIgnoreLaRemarqueDunComposant(): void
    {
        $s = $this->scenario();
        $exec = $this->execAvecPulmo($s);
        $taches = $this->lignesModification($exec, []);
        $taches = array_map(fn ($t) => [...$t, 'remarque' => 'texte'], $taches);

        $exec = ControleExecBusiness::updateExec($exec->id, ['executed_at' => '2026-02-01', 'taches' => $taches]);

        $this->assertNull($exec->execTaches->firstWhere('tache_id', $s['tPulmo']->id)->remarque);
        $this->assertSame('texte', $exec->execTaches->firstWhere('tache_id', $s['tVisuel']->id)->remarque);
    }

    public function testUpdateExecPermetDintervertirDeuxArticles(): void
    {
        // Les lignes s'écrivent une à une : sans précaution, la contrainte
        // d'unicité (exécution, article) refuserait un simple échange.
        $s = $this->scenario();
        $autre = Article::factory()->create(['materiel_type_id' => $s['pulmo']->materiel_type_id, 'statut' => true]);
        ControleTacheMaterielType::create(['controle_tache_id' => $s['tMasque']->id, 'materiel_type_id' => $s['pulmo']->materiel_type_id]);
        $exec = $this->creerExec($s, [
            ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
            ['tache_id' => $s['tMasque']->id, 'article_id' => $autre->id],
        ]);

        $exec = $this->modifierExec($exec, [$s['tPulmo']->id => $autre->id, $s['tMasque']->id => $s['pulmo']->id]);

        $this->assertSame($autre->id, $exec->execTaches->firstWhere('tache_id', $s['tPulmo']->id)->article_id);
        $this->assertSame($s['pulmo']->id, $exec->execTaches->firstWhere('tache_id', $s['tMasque']->id)->article_id);
    }

    public function testControleComposantsRenvoieLesReferencesDeLarticle(): void
    {
        $s = $this->scenario();
        $this->execAvecPulmo($s);
        $autreCacolet = Article::factory()->create(['materiel_type_id' => $s['cacolet']->materiel_type_id, 'statut' => true, 'numero' => 'C-99']);
        ControleExecBusiness::createExec($s['controle']->id, $autreCacolet->id, [
            'executed_at' => '2026-03-01',
            'trigger_type' => 'NON_PERIODIQUE',
            'taches' => [
                ['tache_id' => $s['tPulmo']->id, 'article_id' => $s['pulmo']->id],
                ['tache_id' => $s['tMasque']->id, 'article_id' => $s['masque']->id],
                ['tache_id' => $s['tVisuel']->id, 'statut' => 'OK'],
            ],
        ], Sapeur::factory()->create()->id);
        $s['tPulmo']->update(['nom' => 'Renommée']);

        $response = $this->json('GET', "/api/v2/articles/{$s['pulmo']->id}/controle-composants", [], ['Sis-Key' => 1]);

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertStringStartsWith('2026-03-01', $response->json('data.0.executed_at'));
        $this->assertSame('Contrôle APR', $response->json('data.0.controle_nom'));
        $this->assertSame('Pulmo', $response->json('data.0.composant_nom'));
        $this->assertSame($autreCacolet->id, $response->json('data.0.article_controle.id'));
        $this->assertSame('C-99', $response->json('data.0.article_controle.numero'));
        $this->assertStringStartsWith('2026-01-01', $response->json('data.1.executed_at'));
    }

    public function testControleComposantsVidePourUnArticleJamaisReference(): void
    {
        $s = $this->scenario();
        $this->execAvecPulmo($s);

        $this->json('GET', "/api/v2/articles/{$s['masque']->id}/controle-composants", [], ['Sis-Key' => 1])
            ->assertOk()->assertJsonCount(0, 'data');
    }

    /**
     * @return array{controle: Controle, article: Article, tache: ControleTache}
     */
    private function scenarioMesure(bool $obligatoire): array
    {
        $controle = Controle::create(['nom' => 'Contrôle mesure', 'recurrence_type' => 'NON_PERIODIQUE']);
        $type = MaterielType::factory()->numerote()->create();
        ControleMaterielType::create(['controle_id' => $controle->id, 'materiel_type_id' => $type->id]);
        $tache = ControleTache::create(['controle_id' => $controle->id, 'order' => 1, 'nom' => 'Bouteille droite', 'type' => 'NUMERIC', 'obligatoire' => $obligatoire, 'unit' => 'bar', 'value_min' => 200, 'value_max' => 300]);

        return [
            'controle' => $controle,
            'article' => Article::factory()->create(['materiel_type_id' => $type->id, 'statut' => true]),
            'tache' => $tache,
        ];
    }

    /**
     * @param array{controle: Controle, article: Article, tache: ControleTache} $s
     */
    private function creerExecMesure(array $s, ?float $valeur): ControleExec
    {
        return ControleExecBusiness::createExec($s['controle']->id, $s['article']->id, [
            'executed_at' => '2026-01-01',
            'trigger_type' => 'NON_PERIODIQUE',
            'taches' => [['tache_id' => $s['tache']->id, 'value_measured' => $valeur]],
        ], Sapeur::factory()->create()->id);
    }

    public function testMesureFacultativeVideEstEnregistreeEtConforme(): void
    {
        $exec = $this->creerExecMesure($this->scenarioMesure(false), null);

        $ligne = $exec->execTaches->first();
        $this->assertNull($ligne->value_measured);
        $this->assertNull($ligne->value_in_range);
        $this->assertFalse($ligne->obligatoire_snapshot);
        $this->assertTrue($exec->isConforme());
    }

    public function testMesureFacultativeHorsPlageRendLeControleNonConforme(): void
    {
        $exec = $this->creerExecMesure($this->scenarioMesure(false), 150);

        $this->assertFalse($exec->execTaches->first()->value_in_range);
        $this->assertFalse($exec->isConforme());
    }

    public function testMesureFacultativeAZeroEstUneValeur(): void
    {
        $exec = $this->creerExecMesure($this->scenarioMesure(false), 0);

        $this->assertEquals(0, $exec->execTaches->first()->value_measured);
        $this->assertFalse($exec->isConforme());
    }

    public function testMesureObligatoireVideEstRefusee(): void
    {
        $this->expectException(ArrayException::class);
        $this->creerExecMesure($this->scenarioMesure(true), null);
    }

    public function testUpdateExecVideUneMesureFacultative(): void
    {
        $exec = $this->creerExecMesure($this->scenarioMesure(false), 250);

        $exec = ControleExecBusiness::updateExec($exec->id, [
            'executed_at' => '2026-02-01',
            'taches' => [['id' => $exec->execTaches->first()->id, 'value_measured' => null]],
        ]);

        $this->assertNull($exec->execTaches->first()->value_measured);
        $this->assertTrue($exec->isConforme());
    }

    public function testUpdateExecRefuseDeViderUneMesureDevenueFacultative(): void
    {
        // L'obligation figée à l'exécution fait foi.
        $s = $this->scenarioMesure(true);
        $exec = $this->creerExecMesure($s, 250);
        $s['tache']->update(['obligatoire' => false]);

        $this->expectException(ArrayException::class);
        ControleExecBusiness::updateExec($exec->id, [
            'executed_at' => '2026-02-01',
            'taches' => [['id' => $exec->execTaches->first()->id, 'value_measured' => null]],
        ]);
    }
}
