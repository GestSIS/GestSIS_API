<?php

namespace App\Application\Http\Controllers;

use App\Collections\SapeursExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PublipostageController extends Controller
{
  public function index(Request $request)
  {
    // Paramètres voulu :
    // - Tous les sapeurs actifs(par défault)
    // - Liste des sapeurs spécifique (ids précisé)
    $data = $request->validate([
      'sapeurIds' => 'array',
      'sapeurIds.*' => 'required|integer|distinct',
    ]);
    return Excel::download(new SapeursExport(array_key_exists('sapeurIds', $data) ? $data['sapeurIds'] : []), 'sapeurs.xlsx');
  }
}
