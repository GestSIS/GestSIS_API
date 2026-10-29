<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Les middlewares JWT sont désactivés en environnement de test : ces tests
 * vérifient donc la permission attachée aux routes sensibles plutôt qu'un appel
 * réel, pour qu'un déplacement de route ne les ouvre pas à tort.
 */
class RoutePermissionsTest extends TestCase
{
    /**
     * @return string[]
     */
    private function middlewaresPour(string $method, string $uri): array
    {
        return Route::getRoutes()->match(Request::create($uri, $method))->gatherMiddleware();
    }

    public function testFicheEtDetailDunSapeurExigentSapeurLecture(): void
    {
        // Le détail d'un sapeur contient n° AVS, IBAN, adresse et date de naissance.
        $this->assertContains('jwtTokenRole:sapeur.lecture', $this->middlewaresPour('GET', '/api/v2/sapeurs/1'));
        $this->assertContains('jwtTokenRole:sapeur.lecture', $this->middlewaresPour('GET', '/api/v2/sapeurs/1/fiche'));
    }

    public function testEnregistrerLesParametresDeConvocationExigeExerciceModification(): void
    {
        $this->assertContains('jwtTokenRole:exercice.modification', $this->middlewaresPour('POST', '/api/v2/convocation-param'));
    }

    public function testGetTravauxSansExerciceComptableNExistePas(): void
    {
        // Seul POST travaux (création) existe à cette URL : la liste passe par
        // travaux/{exerciceComptableId}.
        $this->json('GET', '/api/v2/travaux', [], ['Sis-Key' => 1])->assertMethodNotAllowed();
    }
}
