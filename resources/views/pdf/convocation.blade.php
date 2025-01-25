<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="/assets/print.css">

  <title>Convocation</title>
</head>

<body>
  <?php

  use Carbon\Carbon;

  setlocale(LC_TIME, 'fr_CH');
  Carbon::setLocale('fr');
  // Some helper functions

  if (!function_exists('formatDate')) {
    function formatDate($value)
    {
      return Carbon::parse($value)->translatedFormat('D j.m.y');
    }
  }

  if (!function_exists('formatHeure')) {
    function formatHeure($heure)
    {
      return substr($heure, 0, 5);
    }
  }
  if (!function_exists('formatHeureDuree')) {
    function formatHeureDuree($heure, $duree)
    {
      $end = Carbon::parse($heure)->addMinutes($duree);
      return substr($heure, 0, 5) . '&nbsp;-&nbsp;' . $end->format('H:i');
    }
  }
  ?>
  <div class="container-fluid">
    @foreach ($sapeurs as $sapeur)
    <h1>{{ $params['titre'] }}</h1>
    <div style="margin-top: 90px; margin-left: 55%;">
      {{-- {{dd($sapeur, $civilites)}} --}}
      <p class="h3 m-0">{{ $civilites[$sapeur['civilite_id']] }}</p>
      <p class="h3 m-0">{{ $sapeur['nom'] }} {{ $sapeur['prenom'] }}</p>
      <p class="h3 m-0">{{ $sapeur['rue'] }} {{ $sapeur['no_rue'] }}</p>
      <p class="h3 m-0">{{ $localites[$sapeur['localite_id']] }}</p>
    </div>
    <div class="mt-5">
      <p class="text-justify h5">
        @foreach (explode("\n", str_replace(["\r\n", "\n\r", "\r"], "\n", $params['texte_debut'])) as $line)
        {{ $line }}<br />
        @endforeach
      </p>
    </div>
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Date</th>
          <th>Heure</th>
          <th>Événement</th>
          <th>Lieu</th>
        </tr>
      </thead>
      <tbody style="border-bottom: 1px solid rgb(222, 226, 230);">
        <?php
        foreach ($sapeur['exercices'] as $convocation) {
          // dd($convocation['exercice_id'], $exercices);
          $exercice = $exercices[$convocation['exercice_id']];
          $convoque = !!$convocation['convoque'];
          $pourInfo = $params['affichage_pour_info'] && !$convoque;
          $colspan = $pourInfo ? 1 : 2;
        ?>
          <tr>
            <td style="width: 100px !important;">{{ formatDate($exercice['date']) }}</td>
            <td>{!! $params['affichage_duree'] ? formatHeureDuree($exercice['heure'], $exercice['duree']) : formatHeure($exercice['heure']) !!}</td>
            <td>
              {{ $categories[$exercice['exercice_categorie_id']] }} : {{ $exercice['designation'] }}<br />
              {{ $exercice['communications'] }}
            </td>
            <td colspan="{{ $colspan }}">
              {{ $localites[$exercice['localite_id']] }} : {{ $exercice['lieu'] }}
            </td>
            @if ($pourInfo)
            <td><em>{{ $params['texte_pour_info'] }}<em></td>
            @endif
          </tr>
        <?php
        }
        ?>
      </tbody>
    </table>
    <div class="mt-5">
      <p class="text-justify h5">
        @foreach (explode("\n", str_replace(["\r\n", "\n\r", "\r"], "\n", $params['texte_fin'])) as $line)
        {{ $line }}<br />
        @endforeach
      </p>
    </div>
    @if (!$loop->last)
    <div class="page-break"></div>
    @endif
    @endforeach
  </div>
</body>

</html>