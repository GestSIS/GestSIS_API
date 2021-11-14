<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" crossorigin="anonymous">
    <style>
        .page-break {
            page-break-after: always;
        }

        .column-right {
            text-align: right !important;
            padding-right: 1rem !important;
        }

        .sum-row {
            background-color: white !important;
        }
    </style>

    <title>Décompte</title>
</head>
<body>
<div class="">
  
  <h1 class="text-center">{{ $decompte->designation }}</h1>
  <div>Date : {{ formatDate($decompte->date) }}</div>
  <table class="table table-sm table-striped">
    <thead>
      <tr>
        <th colspan="3">Nature du service</th>
        <th>Sapeur</th>
        <th>Tarif</th>
        <th>Qté</th>
        <th class="text-center">Total</th>
      </tr>
    </thead>
    <tbody>

    <?php
    //TODO: Finish decompte impressions
    $nbEcritures = count($ecritures);

    function isExercice($e)
    {
        return $e->exercice_id !== null;
    }
    function isIntervention($e)
    {
        return $e->intervention_id !== null;
    }
    function isAnnuel($e)
    {
        return $e->indemnite_annuel !== null || $e->frais_annuel !== null;
    }

    function formatNumber($value)
    {
        return number_format($value, 2, '.', "'");
    }

    function formatDate($value)
    {
        return str_replace('-', '.', $value);
    }

    function formatTime($value)
    {
        return substr($value, 0, 5);
    }

    function formatTarif($ecriture)
    {
        $tarifMin = $ecriture->solde_min === null ? "" : "($ecriture->solde_min CHF / $ecriture->solde_min_pour H)";
        $tauxSpecial = $ecriture->taux === null ? "" : "* " . $ecriture->taux * 100 . " %";
        return "$tarifMin " . formatNumber($ecriture->tarif) . " $ecriture->unite $tauxSpecial";
    }

    foreach ($ecritures as $index => $ecriture) {
    $last = $index + 1 === $nbEcritures;
    ?>
      <tr>
        {{-- {{dd([$sapeurs, $ecriture->sapeur_id])}} --}}
          <td colspan="3">{{ $ecriture->designation }}</td>
          <td>{{ array_key_exists($ecriture->sapeur_id, $sapeurs) ? $sapeurs[$ecriture->sapeur_id] : '-' }}</td>
          <td>{{ formatTarif($ecriture) }}</td>
          <td>{{ formatNumber($ecriture->quantite) }}</td>
          <td class="column-right">{{ formatNumber($ecriture->total) }}</td>
      </tr>
      
      <?php
    }
    ?>
    </tbody>

    @if($nbEcritures === 0)
        <h1>Aucune écriture</h1>
    @endif
  </table>
</div>
</body>
</html>
