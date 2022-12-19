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
    if (!function_exists('isExercice')) {
      function isExercice($e)
      {
        return intval($e->module) === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_EXERCICE;
      }
    }
    if (!function_exists('isCours')) {
      function isCours($e)
      {
        return intval($e->module) === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_COURS;
      }
    }
    if (!function_exists('isIntervention')) {
      function isIntervention($e)
      {
        return intval($e->module) === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_INTERVENTION;
      }
    }
    if (!function_exists('isAnnuel')) {
      function isAnnuel($e)
      {
        return intval($e->module) === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL;
      }
    }
    if (!function_exists('isDivers')) {
      function isDivers($e)
      {
        return intval($e->module) === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_DIVERS;
      }
    }
    if (!function_exists('isTravail')) {
      function isTravail($e)
      {
        return intval($e->module) === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_FICHE_TRAVAIL;
      }
    }
    if (!function_exists('isAmende')) {
      function isAmende($e)
      {
        return intval($e->module) === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_AMENDE;
      }
    }
    if (!function_exists('isAvsAc')) {
      function isAvsAc($e)
      {
        return intval($e->module) === \App\Domaine\Business\ImputationBusiness::ECRITURE_MODULE_AVS;
      }
    }

    if(!function_exists('sectionTitle')) {
      function sectionTitle($e)
      {
        return $e->categorie;
      }
    }
    if(!function_exists('formatNumber')) {
      function formatNumber($value)
      {
        return number_format($value, 2, '.', "'");
      }
    }

    if(!function_exists('formatDate')) {
      function formatDate($value)
      {
        return implode('.', array_reverse(explode('-', $value)));
      }
    }
    
    if(!function_exists('formatIban')) {
      function formatIban($value)
      {
        return chunk_split(str_replace(' ', '', $value), 4, ' ');
      }
    }

    if(!function_exists('formatTime')) {
      function formatTime($value)
      {
        return $value != "" && !is_null($value) ? substr($value, 0, 5) : "";
      }
    }

    if(!function_exists('formatTarif')) {
      function formatTarif($ecriture)
      {
        $tarifMin = $ecriture->tarif_min === null ? "" : "($ecriture->tarif_min CHF / $ecriture->tarif_min_pour h) +";
        $tauxSpecial = $ecriture->taux === null ? "" : "* " . $ecriture->taux * 100 . "%";
        return "$tarifMin " . formatNumber($ecriture->tarif) . " CHF " . ($ecriture->unite ? "/ $ecriture->unite": "") . " $tauxSpecial";
      }
    }

    $previousEcriture = null;
    $first = true;
    $last = false;
    $nbEcritures = count($ecritures);
    $nbEcritureSections = 0;

    $paiement = null;

    $indexedPaiements = [];
    foreach ($decompte->paiements as $paiement) {
      $indexedPaiements[$paiement->sapeur_id] = $paiement;
    }

    foreach ($ecritures as $index => $ecriture) {
      $last = $index + 1 === $nbEcritures;
      $nextEcriture = $last ? null : $ecritures[$index + 1];

      $debutSapeur = $first || intval($previousEcriture->sapeur_id) !== intval($ecriture->sapeur_id);
      $debutSection = $debutSapeur || intval($previousEcriture->ecriture_categorie_id) !== intval($ecriture->ecriture_categorie_id);
      $debutIntervention = $debutSapeur || intval($previousEcriture->intervention_id) !== intval($ecriture->intervention_id);

      $finSapeur = $last || intval($nextEcriture->sapeur_id) !== intval($ecriture->sapeur_id);
      $finSection = $finSapeur || intval($nextEcriture->ecriture_categorie_id) !== intval($ecriture->ecriture_categorie_id);
      $finIntervention = $finSection || intval($nextEcriture->intervention_id) !== intval($ecriture->intervention_id);

      if ($debutSapeur) {
        $sapeurTotal = 0.0;
        $paiement = $indexedPaiements[$ecriture->sapeur_id];
      }
      
      if ($debutSection) {
        $categorieSousTotal = 0.0;
        $nbEcritureSections = 0;
      }

      if (!isAvsAc($ecriture)) {
        $nbEcritureSections++;
        if ($comptes[$ecriture->compte_id]->produit) {
          $ecriture->total = -$ecriture->total;
        }
        $sapeurTotal += $ecriture->total;
        $categorieSousTotal += $ecriture->total;
      }

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

    @if ($debutSection && !isAvsAc($ecriture))
      <h2>{{ sectionTitle($ecriture) }}</h2>
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

    @if (isCours($ecriture))
      @if ($debutSection)
        <thead>
          <tr>
            <th>Date</th>
            <th>Heures</th>
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
        <td></td>
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

    @if (isTravail($ecriture))
      @if ($debutSection)
        <thead>
          <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Nature du service</th>
            <th>Tarif</th>
            <th>Quantité</th>
            <th class="col-2">Payé / Facturé le</th>
            <th class="text-center col-1">Montant</th>
          </tr>
        </thead>
        <tbody>
      @endif
      <tr>
        <td>{{ formatDate($ecriture->date) }}</td>
        <td></td>
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

    @if (isDivers($ecriture))
      @if ($debutSection)
        <thead>
          <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Nature du service</th>
            <th>Tarif</th>
            <th>Quantité</th>
            <th class="col-2">Payé / Facturé le</th>
            <th class="text-center col-1">Montant</th>
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

    @if ($finSection && $nbEcritureSections > 0)
      <tfoot>
        <tr>
          <th colspan="{{ isAmende($ecriture) ? 5 : 6 }}" class="text-end">Sous-total</th>
          <th class="text-end">{{ formatNumber($categorieSousTotal) }}</th>
        </tr>
        </tbody>
        </table>
    @endif
    @if ($finSapeur)
      <div class="container-fluid">
        <div class="row">
          {{-- TODO: Gérer les écritures divers avec montants négatifs --}}
          {{-- TODO: Ne pas comptabiliser les écritures d'anciens décomptes -> OK à priori --}}
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
          <div class="col-2 p-1">Déjà soldé</div>
          <div class="col-1 text-end p-1 border border-secondary border-bottom-0">
            {{ formatNumber($sapeurTotal - $paiement->total - $paiement->avs_ac) }}
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
      if (!isAvsAc($ecriture)) {
        $previousEcriture = $ecriture;
      }
    }
    ?>

    @if ($nbEcritures === 0)
      <h1>Aucune écriture</h1>
    @endif
  </div>
</body>

</html>
