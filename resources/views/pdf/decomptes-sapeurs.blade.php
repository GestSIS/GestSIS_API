<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/assets/print.css">
  <style>
    tfoot tr,
    tfoot tr th {
      border-width: 0px !important;
    }
  </style>

  <title>Décompte sapeurs</title>
</head>

<body>
  <div class="">
    <?php
    $previousEcriture = null;
    $first = true;
    $last = false;
    $nbEcritures = count($ecritures);

    $wasExercice = false;
    $wasIntervention = false;
    $wasAnnuel = false;

    $categorieSousTotal = 0.0;
    $interventionSousTotal = 0.0;
    $sapeurTotal = 0.0;

    function isExercice($e)
    {
      return $e->module === 1;
    }
    function isIntervention($e)
    {
      return $e->module === 2;
    }
    function isAnnuel($e)
    {
      return $e->module === 3;
    }

    function formatNumber($value)
    {
      return number_format($value, 2, '.', "'");
    }

    function formatDate($value)
    {
      return implode('.', array_reverse(explode('-', $value)));
    }

    function formatTime($value)
    {
      return substr($value, 0, 5);
    }

    function formatTarif($ecriture)
    {
      $tarifMin = $ecriture->tarif_min === null ? "" : "($ecriture->tarif_min CHF / $ecriture->tarif_min_pour H)";
      $tauxSpecial = $ecriture->taux === null ? "" : "* " . $ecriture->taux * 100 . " %";
      return "$tarifMin " . formatNumber($ecriture->tarif) . " $ecriture->unite $tauxSpecial";
    }

    foreach ($ecritures as $index => $ecriture) {
      $last = $index + 1 === $nbEcritures;
      $nextEcriture = $last ? null : $ecritures[$index + 1];

      $isIntervention = isIntervention($ecriture);
      $isExercice = isExercice($ecriture);
      $isAnnuel = isAnnuel($ecriture);

      $newSapeur = $first || $previousEcriture->sapeur_id !== $ecriture->sapeur_id;
      $newCategorie = $newSapeur || $previousEcriture->ecriture_categorie_id !== $ecriture->ecriture_categorie_id;

      $debutSectionExercice = $isExercice && ($newCategorie || !$wasExercice);
      $debutSectionIntervention = $isIntervention && ($newCategorie || !$wasIntervention);
      $debutSectionAnnuel = $isAnnuel && ($newCategorie || !$wasAnnuel);

      $newIntervention = $newSapeur || $previousEcriture->intervention_id !== $ecriture->intervention_id;

      $endSapeur = $last || $nextEcriture->sapeur_id !== $ecriture->sapeur_id;
      $endCategorie = $endSapeur || $nextEcriture->ecriture_categorie_id !== $ecriture->ecriture_categorie_id;

      $finSectionExercice = $endCategorie || $isExercice && !isExercice($nextEcriture);
      $finSectionIntervention = $endCategorie || $isIntervention && !isIntervention($nextEcriture);
      $finSectionAnnuel = $endCategorie || $isAnnuel && !isAnnuel($nextEcriture);

      $finIntervention = $endCategorie || $nextEcriture->intervention_id !== $ecriture->intervention_id;

      $categorieSousTotal = $newCategorie ? 0.0 : $categorieSousTotal;
      $categorieSousTotal += $ecriture->total;
      $interventionSousTotal = $newIntervention ? 0.0 : $interventionSousTotal;
      $interventionSousTotal += $ecriture->total;

      if ($newSapeur){
        $sapeurTotal = 0.0;
      }
      $sapeurTotal += $ecriture->total;


    ?>
    @if ($newSapeur)
      <h1 class="text-center">Décompte de frais</h1>
      <table class="table table-secondary table-responsive table-sm">
        <thead>
          <tr>
            <td>{{ ucfirst($ecriture->civilite) }}</td>
            <td>{{ $ecriture->sapeur }}</td>
            <td>Versement sur</td>
            <td>{{ $ecriture->iban }}</td>
          </tr>
        </thead>
      </table>
      <div></div>
    @endif

    @if ($newCategorie)
      <h2>{{ $ecriture->categorie }}</h2>
      <table class="table table-sm table-striped table-bordered">
    @endif

    @if ($isAnnuel)
      @if ($debutSectionAnnuel)
        <thead>
          <tr>
            <th colspan="3">Nature du service</th>
            <th>Tarif</th>
            <th>Qté</th>
            {{-- <th>Date paiement</th> --}}
            <th class="text-center">Total</th>
          </tr>
        </thead>
        <tbody>
      @endif
      <tr>
        <td colspan="3">{{ $ecriture->designation }}</td>
        <td>{{ formatTarif($ecriture) }}</td>
        <td>{{ formatNumber($ecriture->quantite) }}</td>
        {{-- <td>{{ formatDate($ecriture->date_paiement) }}TODO</td> --}}
        <td class="text-end">{{ formatNumber($ecriture->total) }}</td>
      </tr>
      @if ($finSectionAnnuel)
        </tbody>
      @endif
    @endif

    @if ($isExercice)
      @if ($debutSectionExercice)
        <thead>
          <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Nature du service</th>
            <th>Tarif</th>
            <th>Qté</th>
            {{-- <th>Date paiement</th> --}}
            <th class="text-center">Total</th>
          </tr>
        </thead>
        <tbody>
      @endif
      <tr>
        <td>{{ formatDate($ecriture->date) }}</td>
        <td>{{ formatTime($ecriture->heure) }}</td>
        <td>{{ $ecriture->designation }}</td>
        <td>{{ formatTarif($ecriture) }}</td>
        <td>{{ formatNumber($ecriture->quantite) }}</td>
        {{-- <td>{{ formatDate($ecriture->date_paiement) }}TODO</td> --}}
        <td class="text-end">{{ formatNumber($ecriture->total) }}</td>
      </tr>
      @if ($finSectionExercice)
        </tbody>
      @endif
    @endif

    @if ($isIntervention)
      @if ($debutSectionIntervention)
        <thead>
          <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Intervention</th>
            <th>Tarif</th>
            <th>Qté</th>
            {{-- <th>Date paiement</th> --}}
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
      @endif

      @if ($newIntervention)
        <tr>
          <td>{{ formatDate($ecriture->date) }}</td>
          <td>{{ formatTime($ecriture->heure) }}</td>
          <td colspan="5">{{ $ecriture->designation }}</td>
        </tr>
      @endif
      <tr>
        <td colspan="2"></td>
        <td>TODO Sous-écriture</td>
        <td>{{ formatTarif($ecriture) }}</td>
        <td>{{ formatNumber($ecriture->quantite) }}</td>
        {{-- <td>{{formatDa
                            te( $ecriture->date_paiement) }}TODO</td> --}}
        <td class="text-end">{{ $ecriture->total }}</td>
      </tr>
      @if ($finIntervention)
        <tr>
          <td colspan="7" class="text-end">{{ formatNumber($interventionSousTotal) }}</td>
        </tr>
      @endif

      @if ($finSectionExercice)
        </tbody>
      @endif
    @endif

    @if ($endCategorie)
      <tfoot>
        <tr>
          <th colspan="5" class="text-end">Sous-total</th>
          <th class="text-end">{{ formatNumber($categorieSousTotal) }}</th>
        </tr>
        </tbody>
        </table>
    @endif
    @if ($endSapeur)
      <div class="container-fluid">
        <div class="row">
          <div class="col-5 text-end">Paiement le : {{ formatDate($decompte->date) }}</div>
          <div class="col-5 text-end"><strong>Total</strong></div>
          <div class="col-2 background-secondary text-end p-1">
            <strong>{{ formatNumber($sapeurTotal) }}</strong>
          </div>
        </div>
      </div>
      <div class="page-break"></div>
    @endif
    <?php
      $first = false;
      $previousEcriture = $ecriture;
      $wasExercice = $isExercice;
      $wasIntervention = $isIntervention;
      $wasAnnuel = $isAnnuel;
    }
    ?>

    @if ($nbEcritures === 0)
      <h1>Aucune écriture</h1>
    @endif
  </div>
</body>

</html>
