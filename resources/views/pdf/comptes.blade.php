<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="/assets/print.css">
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
  <title>Pièce justificative pour compte</title>
</head>

<body>
  <div class="container-fluid">
    <h1>Justificatif comptable</h1>
    <? $count = count($comptes) - 1; ?>
    @foreach ($comptes as $index => $compte)
      @component('components/single-compte', ['compte' => $compte, 'sapeurs' => $sapeurs, 'decomptes' => $decomptes])
      @endcomponent
      <div class="page-break"></div>
    @endforeach
    <h2>Récapitulatif</h2>
    <table class="table table-sm table-secondary mt-3">
      <tr>
        <td>Etat au {{ date('d.m.y') }}</td>
      </tr>
    </table>
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Compte</th>
          <th class="text-center">Nb écritures</th>
          <th class="text-center">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $count = 0;
        $total = 0;
        ?>
        @foreach ($comptes as $index => $compte)
          <?php
          $count += $localCount = count($compte->ecritures);
          $total += $localTotal = array_reduce($compte->ecritures->toArray(), fn($acc, $e) => $acc + $e['total'], 0);
          ?>
          <tr>
            <td>{{ $compte->numero }} {{ $compte->designation }}</td>
            <td class="text-end">{{ number_format($localCount, 0, '.', "'") }}</td>
            <td class="text-end">{{ number_format($localTotal, 2, '.', "'") }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th></th>
          <th class="text-end">{{ number_format($count, 0, '.', "'") }}</th>
          <th class="text-end">{{ number_format($total, 2, '.', "'") }}</th>
        </tr>
      </tfoot>
    </table>
  </div>
</body>

</html>
