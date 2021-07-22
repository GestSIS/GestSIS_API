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

    <title>Pièce justificative pour compte</title>
</head>
<body>
<div class="">
    <?php
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
    ?>
    @if ($newSapeur)
        <h1 class="text-center">Décompte de frais</h1>
        <div>{{ ucfirst($ecriture->civilite) }} {{ $ecriture->sapeur }}</div>
    @endif

    @if ($newCategorie)
        <h2>{{ $ecriture->categorie }}</h2>
        <table class="table table-sm table-striped">
            @endif

            @if ($isAnnuel)
                @if ($debutSectionAnnuel)
                    <thead>
                    <tr>
                        <th colspan="3">Nature du service</th>
                        <th>Tarif</th>
                        <th>Qté</th>
                        <th>Date Solde</th>
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
                        <td class="column-right">{{ formatNumber($ecriture->total) }}</td>
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
                        <th>Date Solde</th>
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
                        <td class="column-right">{{ formatNumber($ecriture->total) }}</td>
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
                        <th>Date Solde</th>
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
                        {{-- <td>{{formatDate( $ecriture->date_paiement) }}TODO</td> --}}
                        <td class="column-right">{{ $ecriture->total }}</td>
                    </tr>
                    @if($finIntervention)
                        <tr>
                            <td colspan="7"
                                class="column-right">{{ formatNumber($interventionSousTotal) }}</td>
                        </tr>
                    @endif

                    @if ($finSectionExercice)
                    </tbody>
                @endif
            @endif

            @if ($endCategorie)
                <tbody>
                <tr class="sum-row">
                    <th colspan="6" class="column-right">Sous-total</th>
                    <th class="column-right">{{ formatNumber($categorieSousTotal) }}</th>
                </tr>
                </tbody>
        </table>
    @endif
    @if($endSapeur && !$last)
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

    @if($nbEcritures === 0)
        <h1>Aucune écriture</h1>
    @endif
</div>
</body>
</html>
