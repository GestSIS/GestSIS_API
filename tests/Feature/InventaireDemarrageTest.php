<?php

namespace Tests\Feature;

use App\Domaine\Business\Materiel\InventaireBusiness;
use App\Models\Article;
use App\Models\Emplacement;
use App\Models\Inventaire;
use App\Models\InventaireEmplacement;
use App\Models\MaterielType;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class InventaireDemarrageTest extends TestCase
{
    use DatabaseTransactions;

    public function testDemarrerFigeLePerimetreActifEtCreeLesLignesAttendues(): void
    {
        $racine = Emplacement::factory()->create(['designation' => 'Remorque ARI']);
        $coffre = Emplacement::factory()->create(['designation' => 'Coffre gauche', 'parent_id' => $racine->id, 'tri' => 2]);
        $inactif = Emplacement::factory()->create(['parent_id' => $racine->id, 'statut' => false]);

        $type = MaterielType::factory()->create(['designation' => 'Raccord']);
        $numerote = Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => $coffre->id,
            'numero' => 'RAC-01',
            'compartiment' => 'Haut',
        ]);
        Article::factory()->create(['materiel_type_id' => $type->id, 'emplacement_id' => $racine->id]);
        Article::factory()->supprime()->create(['emplacement_id' => $coffre->id]);
        Article::factory()->create(['emplacement_id' => $inactif->id]);

        $response = $this->demarrer($racine->id, 'Inventaire remorque');

        $response->assertStatus(201);
        $inventaire = Inventaire::findOrFail($response->json('data.id'));
        $this->assertSame('EN_COURS', $inventaire->statut);
        $this->assertSame('Inventaire remorque', $inventaire->designation);

        $this->assertSame($racine->id, $inventaire->racine->emplacement_id);
        $this->assertEqualsCanonicalizing(
            [$racine->id, $coffre->id],
            $inventaire->emplacements->pluck('emplacement_id')->all(),
        );
        $noeudCoffre = $inventaire->emplacements->firstWhere('emplacement_id', $coffre->id);
        $this->assertSame($inventaire->racine->id, $noeudCoffre->parent_id);
        $this->assertSame('Coffre gauche', $noeudCoffre->designation);
        $this->assertSame(2, $noeudCoffre->tri);

        $this->assertSame(2, $inventaire->lignes()->count());
        $ligne = $inventaire->lignes()->where('article_id', $numerote->id)->firstOrFail();
        $this->assertSame('ATTENDU', $ligne->origine);
        $this->assertNull($ligne->statut);
        $this->assertSame($noeudCoffre->id, $ligne->inventaire_emplacement_id);
        $this->assertSame($type->id, $ligne->materiel_type_id);
        $this->assertSame('RAC-01', $ligne->numero);
        $this->assertSame('Raccord', $ligne->designation);
        $this->assertSame('Haut', $ligne->compartiment);
    }

    public function testUnArticleNonNumeroteNaPasDeNumeroFige(): void
    {
        $racine = Emplacement::factory()->create();
        Article::factory()->create(['emplacement_id' => $racine->id, 'numero' => '']);

        $response = $this->demarrer($racine->id);

        $response->assertStatus(201);
        $this->assertNull(Inventaire::findOrFail($response->json('data.id'))->lignes()->first()->numero);
    }

    public function testDemarrerFigeLaTailleEtLaPeremptionDeChaquePiece(): void
    {
        $racine = Emplacement::factory()->create();
        Article::factory()->create(['emplacement_id' => $racine->id, 'numero' => '', 'taille' => ' M ', 'date_peremption' => '2027-03-01']);
        Article::factory()->create(['emplacement_id' => $racine->id, 'numero' => '', 'taille' => '', 'date_peremption' => null]);

        $response = $this->demarrer($racine->id);

        $response->assertStatus(201);
        $lignes = Inventaire::findOrFail($response->json('data.id'))->lignes()->orderBy('id')->get();
        $this->assertSame(['M', null], $lignes->pluck('taille')->all());
        $this->assertSame(['2027-03-01', null], $lignes->map(fn ($l) => $l->date_peremption?->toDateString())->all());
    }

    public function testUnVehiculeFigeSaDesignationPropre(): void
    {
        $racine = Emplacement::factory()->create();
        $type = MaterielType::factory()->create(['designation' => 'Remorque']);
        Article::factory()->create([
            'materiel_type_id' => $type->id,
            'emplacement_id' => $racine->id,
            'designation' => 'Remorque ARI 2',
        ]);

        $response = $this->demarrer($racine->id);

        $response->assertStatus(201);
        $this->assertSame('Remorque ARI 2', Inventaire::findOrFail($response->json('data.id'))->lignes()->first()->designation);
    }

    public function testLeMaterielAttribueNestPasInventorie(): void
    {
        $racine = Emplacement::factory()->create();
        Article::factory()->create(['emplacement_id' => null, 'sapeur_id' => Sapeur::factory()]);

        $response = $this->demarrer($racine->id);

        $response->assertStatus(201);
        $this->assertSame(0, Inventaire::findOrFail($response->json('data.id'))->lignes()->count());
    }

    public function testDemarrerEnregistreLAuteur(): void
    {
        $racine = Emplacement::factory()->create();
        $sapeur = Sapeur::factory()->create();

        $inventaire = InventaireBusiness::demarrer($racine->id, 'Inventaire', now()->toDateString(), $sapeur->id);

        $this->assertSame($sapeur->id, $inventaire->demarre_par_sapeur_id);
    }

    public function testDemarrerSurUnEmplacementDejaEnCoursEstRefuse(): void
    {
        $racine = Emplacement::factory()->create();
        $this->demarrer($racine->id)->assertStatus(201);

        $this->demarrer($racine->id)->assertStatus(422);
        $this->assertSame(1, InventaireEmplacement::where('emplacement_id', $racine->id)->count());
    }

    public function testDemarrerSurUnSousEmplacementDUnInventaireEnCoursEstRefuse(): void
    {
        $racine = Emplacement::factory()->create();
        $enfant = Emplacement::factory()->create(['parent_id' => $racine->id]);
        $this->demarrer($racine->id)->assertStatus(201);

        $this->demarrer($enfant->id)->assertStatus(422);
    }

    public function testDemarrerSurUnParentDUnInventaireEnCoursEstRefuse(): void
    {
        $racine = Emplacement::factory()->create();
        $enfant = Emplacement::factory()->create(['parent_id' => $racine->id]);
        $this->demarrer($enfant->id)->assertStatus(201);

        $this->demarrer($racine->id)->assertStatus(422);
    }

    public function testDemarrerSurUneBrancheVoisineEstAccepte(): void
    {
        $racine = Emplacement::factory()->create();
        $gauche = Emplacement::factory()->create(['parent_id' => $racine->id]);
        $droite = Emplacement::factory()->create(['parent_id' => $racine->id]);
        $this->demarrer($gauche->id)->assertStatus(201);

        $this->demarrer($droite->id)->assertStatus(201);
    }

    public function testDemarrerApresUnInventaireValideEstAccepte(): void
    {
        $racine = Emplacement::factory()->create();
        $inventaire = Inventaire::factory()->valide()->create();
        InventaireEmplacement::factory()->create(['inventaire_id' => $inventaire->id, 'emplacement_id' => $racine->id]);

        $this->demarrer($racine->id)->assertStatus(201);
    }

    public function testDemarrerSurUnEmplacementInactifEstRefuse(): void
    {
        $inactif = Emplacement::factory()->create(['statut' => false]);

        $this->demarrer($inactif->id)->assertStatus(422);
        $this->assertSame(0, InventaireEmplacement::where('emplacement_id', $inactif->id)->count());
    }

    public function testDemarrerAvecUneDateFutureEstRefuse(): void
    {
        $racine = Emplacement::factory()->create();

        $this->demarrer($racine->id, 'Inventaire', now()->addDay()->toDateString())->assertStatus(422);
    }

    public function testDemarrerSansDesignationEstRefuse(): void
    {
        $racine = Emplacement::factory()->create();

        $this->json('POST', '/api/v2/inventaires', [
            'emplacement_id' => $racine->id,
            'date' => now()->toDateString(),
        ], ['Sis-Key' => 1])->assertStatus(422);
    }

    private function demarrer(int $emplacementId, string $designation = 'Inventaire', ?string $date = null): TestResponse
    {
        return $this->json('POST', '/api/v2/inventaires', [
            'emplacement_id' => $emplacementId,
            'designation' => $designation,
            'date' => $date ?? now()->toDateString(),
        ], ['Sis-Key' => 1]);
    }
}
