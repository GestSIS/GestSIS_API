<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/assets/print.css">
  <title>Rapport d'intervention</title>
</head>
<body>
  <div class="container-fluid">
    <h1>Rapport d'intervention</h1>
    {{-- En-tête --}}
    <table class="table table-sm table-striped table-bordered mb-2 mt-3">
      <tr>
        <th>Dates & heures</th>
        <td><strong>Début</strong> : {{ str_replace('-', '.', $intervention->date_debut) }} {{ substr($intervention->heure_debut, 0, 5) }}</td>
        <td><strong>Fin</strong> : {{ str_replace('-', '.', $intervention->date_fin) }} {{ substr($intervention->heure_fin, 0, 5) }}</td>
      </tr>
      <tr>
        <th>Lieu et localité</th>
        <td colspan="2">{{ $intervention->localite->npa }} {{ $intervention->localite->designation }}, {{ $intervention->lieu }}</td>
      </tr>
    </table>
    {{-- {{ dd($intervention); }} --}}
    {{-- Informations générales --}}
    <h2 class="h3 mt-5">Informations générales</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      <tr>
        <th>Type d'intervention</th>
        <td>{{ $intervention->typeIntervention->designation }}</td>
        <th>Chef d'intervention</th>
        <td>{{ $intervention->chefIntervention->nom }} {{ $intervention->chefIntervention->prenom }}</td>
      </tr>
      <tr>
        <th>Objet</th>
        <td colspan="3">{{ $intervention->objet }}</td>
      </tr>
      <tr>
        <th>Statistique féd.</th>
        <td>{{ $intervention->statFederal->designation }}</td>
        <th>Personnes sauvées</th>
        <td>{{ $intervention->sauve_personne }}</td>
      </tr>
      <tr>
        <th>Traitement</th>
        <td>{{ $intervention->traitement->designation }}</td>
        <th>Animaux sauvés</th>
        <td>{{ $intervention->sauve_animaux }}</td>
      </tr>
      <tr>
        <th>Propriétaire</th>
        <td>{!! str_replace(PHP_EOL, '<br>', e($intervention->proprietaire)) !!}</td>
        <th>Responsable</th>
        <td>{!! str_replace(PHP_EOL, '<br>', e($intervention->responsable)) !!}</td>
      </tr>
    </table>
    {{-- Description --}}
    <table class="table table-sm mb-2">
      <tr>
        <th>Description</th>
      </tr>
      <tr>
        <td>{!! str_replace(PHP_EOL, '<br>', e($intervention->description)) !!}</td>
      </tr>
    </table>
    {{-- Véhicules --}}
    @if (array_key_exists("vehicules", $params) && $params["vehicules"])
    <h2 class="h3 mt-3">Véhicules mobilisés</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      @if (count($intervention->vehicules) == 0)
      <tr>
        <td>Aucun véhicule engagé.</td>
      </tr>
      @endif
      @foreach ($intervention->vehicules as $vehicule)
      <tr>
        <td>{{ $vehicules[$vehicule->vehicule_id] }}</td>
      </tr>
      @endforeach
    </table>
    @endif
    {{-- Matériels --}}
    @if (array_key_exists("materiel", $params) && $params["materiel"])
    <h2 class="h3 mt-3">Matériel utilisé</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      @if (count($intervention->materiels) == 0)
      <tr>
        <td>Aucun matériel engagé.</td>
      </tr>
      @endif
      @foreach ($intervention->materiels as $materiel)
      <tr>
        <td>{{ $materiel->quantite }} {{ $materiels[$materiel->materiel_id] }}</td>
      </tr>
      @endforeach
    </table>
    @endif
    {{-- Missions --}}
    @if (array_key_exists("missions", $params) && $params["missions"])
    <h2 class="h3 mt-3">Missions</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      <tr>
        <th>Début</th>
        <th>Quittance</th>
        <th>Titre</th>
        <th>Responsable</th>
      </tr>
      @if (count($intervention->missions) == 0)
      <tr>
        <td colspan="4">Aucune mission</td>
      </tr>
      @endif
      @foreach ($intervention->missions as $mission)
      <tr>
        <td>{{ \Carbon\Carbon::parse($mission->debut)->format('d.m H:i') }}</td>
        <td>{{ \Carbon\Carbon::parse($mission->fin)->format('d.m H:i') }}</td>
        <td>{{ $mission->titre }}</td>
        <td>{{ $mission->sapeur->nom }} {{ $mission->sapeur->prenom }}</td>
      </tr>
      @endforeach
    </table>
    @endif
    {{-- Appels --}}
    @if (array_key_exists("appels", $params) && $params["appels"])
    <h2 class="h3 mt-3">Partenaires contactés</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      <tr>
        <th>Date</th>
        <th>Nom</th>
        <th>Numéro</th>
        <th>Commentaire</th>
      </tr>
      @if (count($intervention->appels) == 0)
      <tr>
        <td colspan="4">Aucun appel effectué.</td>
      </tr>
      @endif
      @foreach ($intervention->appels as $appel)
      <tr>
        <td>{{ \Carbon\Carbon::parse($appel->date)->format('d.m H:i') }}</td>
        <td>{{ $appel->nom }}</td>
        <td>{{ $appel->numero }}</td>
        <td>{{ $appel->commentaire }}</td>
      </tr>
      @endforeach
    </table>
    @endif
    {{-- Groupes --}}
    @if (array_key_exists("groupes", $params) && $params["groupes"])
    <h2 class="h3 mt-3">Groupes alarmés</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      @if (count($intervention->groupes) == 0)
      <tr>
        <td>Aucun groupe</td>
      </tr>
      @endif
      @foreach ($intervention->groupes as $groupe)
      <tr>
        <td>{{ $groupe->no }} {{ $groupe->designation }}</td>
      </tr>
      @endforeach
    </table>
    @endif
    {{-- Présences --}}
    @if (array_key_exists("presences", $params) && $params["presences"])
    <h2 class="h3 mt-3">Présences</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      @if (count($presences) == 0)
      <tr>
        <td>Aucune présence saisie pour cette intervention</td>
      </tr>
      @else
      <thead>
        <tr>
          <th>Sapeur</th>
          <th class="text-center">Quittance</th>
          <th>Présences</th>
          @if (array_key_exists("montants", $params) && $params["montants"])
          <th>Total</th>
          @endif
        </tr>
      </thead>
      @endif
      @foreach ($presences as $sapeur)
      <tr>
        <td>{{ $sapeur['nom'] }} {{ $sapeur['prenom'] }}</td>
        <td>
          <div class="form-check text-center">
            <label class="form-check-label" for="remplace-{{$sapeur['id']}}"><input type="checkbox" class="form-check-input" id="remplace-{{$sapeur['id']}}" @if (array_key_exists($sapeur['id'], $quittances))
              checked="checked"
              @endif
              >&#8203;</label>
            </div>
          </td>
          @foreach ($sapeur['presences'] as $presence)
          @if($loop->index != 0)
        </tr>
        <tr>
          <td colspan="2"></td>
          @endif
          <td>{{ \Carbon\Carbon::parse($presence['debut'])->format('d.m H:i') }} - {{ \Carbon\Carbon::parse($presence['fin'])->format('H:i') }}</td>
          @if ($loop->index == 0 && array_key_exists("montants", $params) && $params["montants"])
          <td>{{ $ecritures[$sapeur['id']] }} CHF</td>
          @endif
          @endforeach
        </tr>
        @endforeach
        <thead>
          <tr>
            <th colspan="3">Nombre sapeur : {{ count($presences) }}</th>
            @if (array_key_exists("montants", $params) && $params["montants"])
            <th>Total : {{ $ecritures['total'] }} CHF</th>
            @endif
          </tr>
        </thead>
      </table>
      @endif
    </table>
  </div>
</body>

</html>