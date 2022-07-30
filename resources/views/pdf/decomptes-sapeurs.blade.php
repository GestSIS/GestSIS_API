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
    // public const ECRITURE_MODULE_DIVERS = 0;
    // public const ECRITURE_MODULE_EXERCICE = 1;
    // public const ECRITURE_MODULE_INTERVENTION = 2;
    // public const ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL = 3;
    // public const ECRITURE_MODULE_AVS = 4;
    // public const ECRITURE_MODULE_AMENDE = 5;
    // public const ECRITURE_MODULE_DECOMPTE_HEURE = 6;
    // public const ECRITURE_MODULE_COURS = 7;
    // public const ECRITURE_MODULE_REMBOURSEMENT = 8;
    function isExercice($e)
    {
      return $e->module === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_EXERCICE;
    }
    function isIntervention($e)
    {
      return $e->module === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_INTERVENTION;
    }
    function isAnnuel($e)
    {
      return $e->module === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL;
    }
    function isDivers($e)
    {
      return $e->module === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_DIVERS;
    }
    function isAmende($e)
    {
      return $e->module === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_AMENDE;
    }

    function formatNumber($value)
    {
      return number_format($value, 2, '.', "'");
    }

    function formatDate($value)
    {
      return implode('.', array_reverse(explode('-', $value)));
    }
    
    function formatIban($value)
    {
      return chunk_split(str_replace(' ', '', $value), 4, ' ');
    }

    function formatTime($value)
    {
      return substr($value, 0, 5);
    }

    function formatTarif($ecriture)
    {
      $tarifMin = $ecriture->tarif_min === null ? "" : "($ecriture->tarif_min CHF / $ecriture->tarif_min_pour h) +";
      $tauxSpecial = $ecriture->taux === null ? "" : "* " . $ecriture->taux * 100 . "%";
      return "$tarifMin " . formatNumber($ecriture->tarif) . " CHF / $ecriture->unite $tauxSpecial";
    }

    $previousEcriture = null;
    $first = true;
    $last = false;
    $nbEcritures = count($ecritures);

    $categorieSousTotal = 0.0;
    $interventionSousTotal = 0.0;
    $sapeurTotal = 0.0;
    $sapeurTotalAvs = 0.0;
    $sapeurId = null;
    $paiement = null;

    $indexedPaiements = [];
    foreach ($decompte->paiements  as $paiement) {
      $indexedPaiements[$paiement->sapeur_id] = $paiement;
    }

    foreach ($ecritures as $index => $ecriture) {
      if ($sapeurId == null || $sapeurId != $ecriture->sapeur_id) {
        $sapeurId = $ecriture->sapeur_id;
        $paiement = $indexedPaiements[$ecriture->sapeur_id];
      }

      $last = $index + 1 === $nbEcritures;
      $nextEcriture = $last ? null : $ecritures[$index + 1];

      $debutSapeur = $first || $previousEcriture->sapeur_id !== $ecriture->sapeur_id;
      $debutSection = $debutSapeur || $previousEcriture->ecriture_categorie_id !== $ecriture->ecriture_categorie_id;
      $debutIntervention = $debutSapeur || $previousEcriture->intervention_id !== $ecriture->intervention_id;

      $finSapeur = $last || $nextEcriture->sapeur_id !== $ecriture->sapeur_id;
      $finSection = $finSapeur || $nextEcriture->ecriture_categorie_id !== $ecriture->ecriture_categorie_id;
      $finIntervention = $finSection || $nextEcriture->intervention_id !== $ecriture->intervention_id;

      $categorieSousTotal = $debutSection ? 0.0 : $categorieSousTotal;
      $categorieSousTotal += $ecriture->total;
      $interventionSousTotal = $debutIntervention ? 0.0 : $interventionSousTotal;
      $interventionSousTotal += $ecriture->total;

      if ($debutSapeur){
        $sapeurTotal = 0.0;
      }
      // TODO: Gérer les amendes ainsi que les 
      $sapeurTotal += $ecriture->total;
    ?>
    @if ($debutSapeur)
      <h1 class="text-center">Décompte de frais</h1>
      <table class="table table-secondary table-responsive table-sm">
        <thead>
          <tr>
            <td><strong>{{ ucfirst($ecriture->civilite) }}</strong></td>
            <td><strong>{{ $ecriture->sapeur }}</strong></td>
            <td class="text-end"><strong>Versement sur :</strong></td>
            <td><strong>{{ formatIban($ecriture->iban) }}</strong></td>
          </tr>
        </thead>
      </table>
      <div></div>
    @endif

    @if ($debutSection)
      <h2>{{ $ecriture->categorie }}</h2>
      <table class="table table-sm table-striped table-bordered">
    @endif

    @if (isAnnuel($ecriture))
      @if ($debutSection)
        <thead>
          <tr>
            <th colspan="3">Nature du service</th>
            <th>Tarif</th>
            <th class="col-1">Quantité</th>
            <th class="col-2">Payé le</th>
            <th class="text-center col-1">Total</th>
          </tr>
        </thead>
        <tbody>
      @endif
      <tr>
        <td colspan="3">{{ $ecriture->designation }}</td>
        <td>{{ formatTarif($ecriture) }}</td>
        <td>{{ formatNumber($ecriture->quantite) }}</td>
        <td>{{ formatDate($decomptes[$ecriture->decompte_id]->date) }}</td>
        <td class="text-end">{{ formatNumber($ecriture->total) }}</td>
      </tr>
      @if ($finSection)
        </tbody>
      @endif
    @endif

    @if (isExercice($ecriture))
      @if ($debutSection)
        <thead>
          <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Nature du service</th>
            <th>Tarif</th>
            <th class="col-1">Quantité</th>
            <th class="col-2">Payé le</th>
            <th class="text-center col-1">Total</th>
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
        <td>{{ formatDate($decomptes[$ecriture->decompte_id]->date) }}</td>
        <td class="text-end">{{ formatNumber($ecriture->total) }}</td>
      </tr>
      @if ($finSection)
        </tbody>
      @endif
    @endif

    @if (isAmende($ecriture))
      @if ($debutSection)
        <thead>
          <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Nature du service</th>
            <th>Motif</th>
            <th class="col-2">Facturé le</th>
            <th class="text-center col-1">Montant</th>
          </tr>
        </thead>
        <tbody>
      @endif
      <tr>
        <td>{{ formatDate($ecriture->date) }}</td>
        <td>{{ formatTime($ecriture->heure) }}</td>
        <td>{{ $ecriture->designation }}</td>
        <td>{{ $ecriture->complement }}</td>
        <td>{{ formatDate($decomptes[$ecriture->decompte_id]->date) }}</td>
        <td class="text-end">{{ formatNumber($ecriture->total) }}</td>
      </tr>
      @if ($finSection)
        </tbody>
      @endif
    @endif

    @if (isDivers($ecriture))
      @if ($debutSection)
        <thead>
          <tr>
            <th>Date</th>
            <th>Désignation</th>
            <th>Tarif</th>
            <th>Quantité</th>
            <th class="col-2">Payé /Facturé le</th>
            <th class="text-center col-1">Montant</th>
          </tr>
        </thead>
        <tbody>
      @endif
      <tr>
        <td>{{ formatDate($ecriture->date) }}</td>
        <td>{{ $ecriture->designation }}</td>
        <td>{{ formatTarif($ecriture) }}</td>
        <td>{{ formatNumber($ecriture->quantite) }}</td>
        <td>{{ formatDate($decomptes[$ecriture->decompte_id]->date) }}</td>
        <td class="text-end">{{ formatNumber($ecriture->total) }}</td>
      </tr>
      @if ($finSection)
        </tbody>
      @endif
    @endif

    @if (isIntervention($ecriture))
      @if ($debutSection)
        <thead>
          <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Intervention</th>
            <th>Tarif</th>
            <th class="col-1">Quantité</th>
            <th class="col-2">Payé le</th>
            <th class="col-1">Total</th>
          </tr>
        </thead>
        <tbody>
      @endif

      @if ($debutIntervention)
        <tr>
          <td>{{ formatDate($ecriture->date) }}</td>
          <td>{{ formatTime($ecriture->heure) }}</td>
          <td colspan="5">{{ $ecriture->designation }}</td>
        </tr>
      @endif
      <tr>
        <td colspan="2"></td>
        <td>{{ $ecriture->taux_description }}</td>
        <td>{{ formatTarif($ecriture) }}</td>
        <td>{{ formatNumber($ecriture->quantite) }}</td>
        <td>{{ formatDate($decomptes[$ecriture->decompte_id]->date) }}</td>
        <td class="text-end">{{ $ecriture->total }}</td>
      </tr>
      @if ($finSection)
        </tbody>
      @endif
    @endif

    @if ($finSection)
      <tfoot>
        <tr>
          <th colspan="{{ isAmende($ecriture) || isDivers($ecriture) ? 5 : 6 }}" class="text-end">Sous-total</th>
          <th class="text-end">{{ formatNumber($categorieSousTotal) }}</th>
        </tr>
        </tbody>
        </table>
    @endif
    @if ($finSapeur)
      <div class="container-fluid">
        <div class="row">
          {{-- TODO: Gérer les écritures divers avec montants négatifs --}}
          {{-- TODO: Merge écritures pour exercices avec solde + indemnité -> A priori OK comparé à GestSIS v1.0 --}}
          {{-- TODO: Ajout résumé des paiements en clôture du document --}}
          <div class="col-9"></div>
          <div class="col-2 p-1">Total</div>
          <div class="col-1 text-end p-1 bg-light border border-secondary border-bottom-0">
            {{ formatNumber($sapeurTotal) }}
          </div>
        </div>
        <div class="row">
          <div class="col-9"></div>
          <div class="col-2 p-1">Déductions AVS/AC</div>
          <div class="col-1 text-end p-1 border border-secondary border-bottom-0">
            {{ formatNumber($paiement->avs_ac) }}</div>
        </div>
        <div class="row">
          <div class="col-9"></div>
          <div class="col-2 p-1">Déjà soldé + Amende</div>
          <div class="col-1 text-end p-1 border border-secondary border-bottom-0">
            {{ formatNumber($sapeurTotal - $paiement->total) }}
          </div>
        </div>
        <div class="row">
          <div class="col-6 text-end p-1">Paiement le : {{ formatDate($decompte->date) }}</div>
          <div class="col-3"></div>
          <div class="col-2 p-1">Total versé</div>
          <div class="col-1 text-end p-1 bg-light border border-secondary">{{ formatNumber($paiement->total) }}</div>
        </div>
      </div>
      <div class="page-break"></div>
    @endif
    <?php
      $first = false;
      $previousEcriture = $ecriture;
    }
    ?>

    @if ($nbEcritures === 0)
      <h1>Aucune écriture</h1>
    @endif
  </div>
</body>

</html>
