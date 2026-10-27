<?php

namespace Tests\Feature;

use App\Application\Http\Controllers\MesExercicesController;
use App\Domaine\Business\TravauxBusiness;
use App\Domaine\Business\TravauxParamBusiness;
use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\Sapeur;
use App\Models\Travail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Les fiches de travail liées à un exercice remplacent les heures supplémentaires
 * dans les réponses de présence (clé `travaux`). La clé `heures` reste présente, vide,
 * pour les versions de l'appli mobile qui la lisent encore.
 */
class ExerciceTravauxTest extends TestCase
{
    use DatabaseTransactions;

    protected Exercice $exercice;
    protected int $convoqueId;
    protected int $nonConvoqueId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exercice = Exercice::factory()->create(['statut' => 2, 'date' => now()->toDateString()]);
        $this->convoqueId = Sapeur::factory()->create()->id;
        $this->nonConvoqueId = Sapeur::factory()->create()->id;
        ExerciceSapeur::create([
            'exercice_id' => $this->exercice->id, 'sapeur_id' => $this->convoqueId,
            'convoque' => true, 'present' => true, 'absent' => false, 'remplace' => false, 'amende' => false,
        ]);

        $typeId = TravauxParamBusiness::ajouterType([
            'designation' => 'Préparation',
            'actif' => true,
            'ecriture_categorie_id' => 1,
            'type_unite_id' => 2,
            'lien_exercice' => true,
            'fonctions' => [['type' => 1, 'tarif' => 30.00, 'compte_id' => 1, 'fonction_id' => null]],
        ])->id;
        foreach ([$this->convoqueId, $this->nonConvoqueId] as $sapeurId) {
            Travail::create([
                'designation' => 'Préparation',
                'date' => $this->exercice->date,
                'date_demande' => $this->exercice->date,
                'quantite' => 1.5,
                'justification' => '',
                'statut' => TravauxBusiness::TRAVAIL_STATUT_SAISI,
                'sapeur_id' => $sapeurId,
                'auteur_id' => $sapeurId,
                'travail_type_id' => $typeId,
                'exercice_comptable_id' => $this->exercice->exercice_comptable_id,
                'exercice_id' => $this->exercice->id,
            ]);
        }
    }

    public function testPresencesExposentLesTravauxParSapeur()
    {
        $sapeurs = collect($this->json('GET', "/api/v2/exercices/{$this->exercice->id}/sapeurs")->json('data'))->keyBy('sapeur_id');

        $this->assertSame([], $sapeurs[$this->convoqueId]['heures']);
        $this->assertCount(1, $sapeurs[$this->convoqueId]['travaux']);
        // Sapeur avec une fiche sans être convoqué : présent dans la liste, non convoqué
        $this->assertFalse($sapeurs[$this->nonConvoqueId]['convoque']);
        $this->assertCount(1, $sapeurs[$this->nonConvoqueId]['travaux']);
    }

    public function testDerniersExercicesExposentLesTravaux()
    {
        $exercice = collect($this->json('GET', '/api/v2/exercices-derniers')->json('data'))->firstWhere('id', $this->exercice->id);
        $sapeurs = collect($exercice['sapeurs'])->keyBy('sapeur_id');

        $this->assertSame([], $sapeurs[$this->convoqueId]['heures']);
        $this->assertCount(1, $sapeurs[$this->convoqueId]['travaux']);
        $this->assertCount(1, $sapeurs[$this->nonConvoqueId]['travaux']);
    }

    public function testMesExercicesExposentLesTravaux()
    {
        $request = Request::create('/api/v2/mes-exercices');
        $request->attributes->set('sapeurId', $this->nonConvoqueId);

        $data = app(MesExercicesController::class)->index($request, $this->exercice->exercice_comptable_id)->getData(true)['data'];
        $exercice = collect($data)->firstWhere('id', $this->exercice->id);

        $this->assertNull($exercice['presence']);
        $this->assertSame([], $exercice['heures']);
        $this->assertCount(1, $exercice['travaux']);
    }

    public function testTypesHeuresExerciceVidesPourLAppliMobile()
    {
        $response = $this->json('GET', '/api/v2/heure-exercice-types');

        $response->assertStatus(200);
        $this->assertSame([], $response->json('data'));
    }

    public function testPresencesDeLAncienneAppliAvecHeuresAcceptees()
    {
        $response = $this->json('POST', "/api/v2/exercices/{$this->exercice->id}/presences", [
            'sapeurs' => [[
                'id' => ExerciceSapeur::where('exercice_id', $this->exercice->id)->where('sapeur_id', $this->convoqueId)->value('id'),
                'sapeur_id' => $this->convoqueId,
                'convoque' => true,
                'present' => true,
                'absent' => false,
                'remplace' => false,
                'excuse_type_id' => null,
                'heures' => [['id' => null, 'quantite' => 2, 'heure_exercice_type_id' => 1]],
            ]],
        ]);

        $response->assertStatus(200);
        $this->assertSame(1, Travail::where('exercice_id', $this->exercice->id)->where('sapeur_id', $this->convoqueId)->count());
    }
}
