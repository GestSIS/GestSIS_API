<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="/assets/print.css">
  <style>
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
  <div class="container-fluid">

    <h1 class="text-center">{{ $decompte->designation }}</h1>
    <div>Date : {{ formatDate($decompte->date) }}</div>
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Date</th>
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

    function formatNumber($value)
    {
        return number_format($value, 2, '.', "'");
    }

    function formatDate($value)
    {
        return str_replace('-', '.', $value);
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
          <td>{{ formatDate($ecriture->date) }}</td>
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

      @if ($nbEcritures === 0)
        <h1>Aucune écriture</h1>
      @endif
    </table>
  </div>
</body>

</html>
