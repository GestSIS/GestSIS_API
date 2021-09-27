<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\PublipostageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublipostageController extends Controller
{
  protected $service;

  public function __construct(PublipostageService $service)
  {
    $this->service = $service;
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index(Request $request)
  {
    // Paramètres voulu :
    // - Tous les sapeurs actifs(par défault)
    // - Liste des sapeurs spécifique (ids précisé)
    $data = $request->validate([
      'sapeurIds' => 'array',
      'sapeurIds.*' => 'required|integer|distinct',
    ]);
    return $this->service->sapeurs(array_key_exists('sapeurIds', $data) ? $data['sapeurIds'] : []);
  }
}
