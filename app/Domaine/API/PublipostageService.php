<?php

namespace App\Domaine\API;

use App\Infrastructure\Collections\SapeursExport;
use Maatwebsite\Excel\Facades\Excel;

class PublipostageService
{
  public function sapeurs($params)
  {
    return Excel::download(new SapeursExport($params), 'sapeurs.xlsx');
  }
}
