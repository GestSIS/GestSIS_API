<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleExec;
use App\Models\ControleTache;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Suppression d'un contrôle et d'une exécution : le DELETE doit réellement
 * supprimer (régression où destroy() renvoyait 204 sans rien faire).
 */
class ControleDestroyTest extends TestCase
{
    use DatabaseTransactions;

    private function creerControle(): Controle
    {
        return Controle::create([
            'nom' => 'Contrôle test',
            'recurrence_type' => 'PERIODIQUE',
            'recurrence_value' => 12,
        ]);
    }

    private function creerExec(Controle $controle): ControleExec
    {
        return ControleExec::create([
            'controle_id' => $controle->id,
            'article_id' => Article::factory()->create(['materiel_type_id' => MaterielType::factory()->create()->id])->id,
            'executed_at' => now(),
            'executed_by' => Sapeur::factory()->create()->id,
            'trigger_type' => 'PERIODIQUE',
        ]);
    }

    public function testDestroySupprimeLeControleEtSesTaches(): void
    {
        $controle = $this->creerControle();
        $tache = ControleTache::create([
            'controle_id' => $controle->id,
            'order' => 1,
            'nom' => 'Tâche',
            'type' => 'BOOLEAN',
        ]);

        $this->json('DELETE', "/api/v2/controles/{$controle->id}", [], ['Sis-Key' => 1])
            ->assertNoContent();

        $this->assertModelMissing($controle);
        $this->assertModelMissing($tache);
    }

    public function testDestroyRefuseUnControleAyantDesExecutions(): void
    {
        $controle = $this->creerControle();
        $this->creerExec($controle);

        $this->json('DELETE', "/api/v2/controles/{$controle->id}", [], ['Sis-Key' => 1])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Impossible de supprimer un contrôle ayant des exécutions enregistrées');

        $this->assertModelExists($controle);
    }

    public function testDestroyExecSupprimeLExecution(): void
    {
        $exec = $this->creerExec($this->creerControle());

        $this->json('DELETE', "/api/v2/controle-execs/{$exec->id}", [], ['Sis-Key' => 1])
            ->assertNoContent();

        $this->assertModelMissing($exec);
    }
}
