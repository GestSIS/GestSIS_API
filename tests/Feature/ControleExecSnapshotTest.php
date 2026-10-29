<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\ControleExecBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleExec;
use App\Models\ControleExecTache;
use App\Models\ControleMaterielType;
use App\Models\ControleTache;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Une exécution enregistrée ne dépend plus de la configuration actuelle des
 * tâches de son contrôle : nom, type, unité, ordre et bornes sont figés sur
 * chaque ligne de résultat.
 */
class ControleExecSnapshotTest extends TestCase
{
    use DatabaseTransactions;

    private function creerControleAvecTachePression(): Controle
    {
        $controle = Controle::create([
            'nom' => 'Contrôle test',
            'recurrence_type' => 'PERIODIQUE',
            'recurrence_value' => 12,
        ]);

        ControleTache::create([
            'controle_id' => $controle->id,
            'order' => 1,
            'nom' => 'Pression',
            'type' => 'NUMERIC',
            'unit' => 'bar',
            'value_min' => 2,
            'value_max' => 3,
        ]);

        return $controle->load('taches');
    }

    private function creerArticleRattache(Controle $controle): Article
    {
        $type = MaterielType::factory()->create();

        ControleMaterielType::create([
            'controle_id' => $controle->id,
            'materiel_type_id' => $type->id,
        ]);

        return Article::factory()->create(['materiel_type_id' => $type->id]);
    }

    private function creerExecPression(Controle $controle, float $valeur = 2.5): ControleExec
    {
        $article = $this->creerArticleRattache($controle);

        return ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => '2026-01-01',
            'trigger_type' => 'PERIODIQUE',
            'remarque_globale' => null,
            'date_echeance' => '2027-01-01',
            'taches' => [
                ['tache_id' => $controle->taches->first()->id, 'value_measured' => $valeur, 'remarque' => null],
            ],
        ], Sapeur::factory()->create()->id);
    }

    private function assertLigneFigeeIntacte(ControleExecTache $ligne): void
    {
        $this->assertSame('Pression', $ligne->nom_snapshot);
        $this->assertSame('NUMERIC', $ligne->type_snapshot);
        $this->assertSame('bar', $ligne->unit_snapshot);
        $this->assertEquals(2, $ligne->value_min_snapshot);
        $this->assertEquals(3, $ligne->value_max_snapshot);
    }

    public function testCreateExecFigeNomTypeEtUnite(): void
    {
        $controle = $this->creerControleAvecTachePression();

        $exec = $this->creerExecPression($controle);

        $this->assertLigneFigeeIntacte($exec->execTaches->first()->fresh());
    }

    public function testModifierLaTacheNeModifiePasLesChampsFigesDuneExecution(): void
    {
        $controle = $this->creerControleAvecTachePression();
        $exec = $this->creerExecPression($controle);

        $controle->taches->first()->update([
            'nom' => 'Renommée',
            'type' => 'BOOLEAN',
            'unit' => null,
            'value_min' => null,
            'value_max' => null,
        ]);

        $this->assertLigneFigeeIntacte($exec->execTaches->first()->fresh());
    }

    public function testSupprimerUneTacheAyantDesResultatsViaPutControle(): void
    {
        $controle = $this->creerControleAvecTachePression();
        $exec = $this->creerExecPression($controle);

        $response = $this->json('PUT', "/api/v2/controles/{$controle->id}", [
            'nom' => 'Contrôle test',
            'recurrence_type' => 'PERIODIQUE',
            'recurrence_value' => 12,
            'taches' => [],
            'materiel_type_ids' => [],
        ], ['Sis-Key' => 1]);

        $response->assertOk();
        $ligne = $exec->execTaches->first()->fresh();
        $this->assertNull($ligne->tache_id);
        $this->assertLigneFigeeIntacte($ligne);
    }

    public function testSupprimerUneTacheAyantDesResultatsViaDelete(): void
    {
        $controle = $this->creerControleAvecTachePression();
        $exec = $this->creerExecPression($controle);
        $tache = $controle->taches->first();

        $response = $this->json('DELETE', "/api/v2/controles/{$controle->id}/taches/{$tache->id}", [], ['Sis-Key' => 1]);

        $response->assertNoContent();
        $ligne = $exec->execTaches->first()->fresh();
        $this->assertNull($ligne->tache_id);
        $this->assertLigneFigeeIntacte($ligne);
    }

    /**
     * @param array<int, array{id: int, statut?: ?string, value_measured?: ?float, remarque?: ?string}> $taches
     */
    private function modifierExec(ControleExec $exec, array $taches): ControleExec
    {
        return ControleExecBusiness::updateExec($exec->id, [
            'executed_at' => '2026-02-01',
            'remarque_globale' => null,
            'date_echeance' => '2027-02-01',
            'taches' => $taches,
        ]);
    }

    public function testUpdateExecApresChangementDeConfigurationSappuieSurLesLignesFigees(): void
    {
        $controle = $this->creerControleAvecTachePression();
        $exec = $this->creerExecPression($controle);
        $ligne = $exec->execTaches->first();

        $controle->taches->first()->update(['type' => 'BOOLEAN', 'order' => 5, 'value_min' => null, 'value_max' => null]);
        ControleTache::create(['controle_id' => $controle->id, 'order' => 1, 'nom' => 'Nouvelle', 'type' => 'BOOLEAN']);

        $exec = $this->modifierExec($exec, [['id' => $ligne->id, 'value_measured' => 3.5, 'remarque' => null]]);

        $this->assertCount(1, $exec->execTaches);
        $ligne = $ligne->fresh();
        $this->assertEquals(3.5, $ligne->value_measured);
        $this->assertFalse($ligne->value_in_range);
        $this->assertSame(1, $ligne->task_order_snapshot);
        $this->assertLigneFigeeIntacte($ligne);
    }

    public function testUpdateExecApresSuppressionDeTacheFonctionne(): void
    {
        $controle = $this->creerControleAvecTachePression();
        $exec = $this->creerExecPression($controle);
        $ligne = $exec->execTaches->first();
        $controle->taches->first()->delete();

        $this->modifierExec($exec, [['id' => $ligne->id, 'value_measured' => 2.2, 'remarque' => 'ok']]);

        $ligne = $ligne->fresh();
        $this->assertNull($ligne->tache_id);
        $this->assertEquals(2.2, $ligne->value_measured);
        $this->assertTrue($ligne->value_in_range);
        $this->assertSame('ok', $ligne->remarque);
    }

    public function testUpdateExecRefuseUneLigneManquante(): void
    {
        $controle = $this->creerControleAvecTachePression();
        ControleTache::create(['controle_id' => $controle->id, 'order' => 2, 'nom' => 'Visuel', 'type' => 'BOOLEAN']);
        $controle->load('taches');
        $article = $this->creerArticleRattache($controle);
        $exec = ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => '2026-01-01',
            'trigger_type' => 'PERIODIQUE',
            'date_echeance' => '2027-01-01',
            'taches' => [
                ['tache_id' => $controle->taches[0]->id, 'value_measured' => 2.5],
                ['tache_id' => $controle->taches[1]->id, 'statut' => 'OK'],
            ],
        ], Sapeur::factory()->create()->id);

        $this->expectException(ArrayException::class);
        $this->modifierExec($exec, [['id' => $exec->execTaches->first()->id, 'value_measured' => 2.5]]);
    }

    public function testUpdateExecRefuseUneLigneDuneAutreExecution(): void
    {
        $controle = $this->creerControleAvecTachePression();
        $exec = $this->creerExecPression($controle);
        $autreExec = $this->creerExecPression($controle);

        $this->expectException(ArrayException::class);
        $this->modifierExec($exec, [['id' => $autreExec->execTaches->first()->id, 'value_measured' => 2.5]]);
    }

    public function testUpdateExecRefuseUnIdDeLigneEnDouble(): void
    {
        $controle = $this->creerControleAvecTachePression();
        $exec = $this->creerExecPression($controle);
        $id = $exec->execTaches->first()->id;

        $this->expectException(ArrayException::class);
        $this->modifierExec($exec, [['id' => $id, 'value_measured' => 2.5], ['id' => $id, 'value_measured' => 2.6]]);
    }

    public function testUpdateExecDunControleSansTacheResteValideApresAjoutDeTache(): void
    {
        $controle = Controle::create(['nom' => 'Sans tâche', 'recurrence_type' => 'PERIODIQUE', 'recurrence_value' => 12]);
        $article = $this->creerArticleRattache($controle);
        $exec = ControleExecBusiness::createExec($controle->id, $article->id, [
            'executed_at' => '2026-01-01',
            'trigger_type' => 'PERIODIQUE',
            'date_echeance' => '2027-01-01',
            'taches' => [],
        ], Sapeur::factory()->create()->id);
        ControleTache::create(['controle_id' => $controle->id, 'order' => 1, 'nom' => 'Ajoutée', 'type' => 'BOOLEAN']);

        $exec = $this->modifierExec($exec, []);

        $this->assertCount(0, $exec->execTaches);
        $this->assertSame('2026-02-01', $exec->executed_at->toDateString());
    }

    public function testPutControleExecAccepteLeNouveauContrat(): void
    {
        $controle = $this->creerControleAvecTachePression();
        $exec = $this->creerExecPression($controle);
        $payload = ['executed_at' => '2026-02-01', 'date_echeance' => '2027-02-01'];

        $this->json('PUT', "/api/v2/controle-execs/{$exec->id}", [
            ...$payload,
            'taches' => [['id' => $exec->execTaches->first()->id, 'value_measured' => 2.8]],
        ], ['Sis-Key' => 1])->assertOk();

        $this->json('PUT', "/api/v2/controle-execs/{$exec->id}", [
            ...$payload,
            'taches' => [['value_measured' => 2.8]],
        ], ['Sis-Key' => 1])->assertUnprocessable();
    }
}
