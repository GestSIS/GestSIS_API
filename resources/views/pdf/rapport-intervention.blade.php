<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
  <style>
    .page-break {
      page-break-after: always;
    }
  </style>

  <title>Rapport d'intervention</title>
</head>
<body>
  <div class="container-fluid">
    <h1>Rapport d'intervention</h1>
    {{-- En-tête --}}
    <table class="table table-sm mb-2">
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
    <h2 class="h4">Informations générales</h2>
    <table class="table table-sm mb-2">
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
        <td>{!! str_replace('\n', '<br>', e($intervention->proprietaire)) !!}</td>
        <th>Responsable</th>
        <td>{!! str_replace('\n', '<br>', e($intervention->responsable)) !!}</td>
      </tr>
    </table>
    {{-- Description --}}
    <table class="table table-sm mb-2">
      <tr>
        <th>Description</th>
      </tr>
      <tr>
        <td>{!! str_replace('\n', '<br>', e($intervention->description)) !!}</td>
      </tr>
    </table>
    {{-- Véhicules --}}
    @if (array_key_exists("vehicules", $params))
    <h2 class="h4">Véhicules mobilisés</h2>
    <table class="table table-sm mb-2">
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
    @if (array_key_exists("vehicules", $params))
    <h2 class="h4">Matériel utilisé</h2>
    <table class="table table-sm mb-2">
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
    @if (array_key_exists("missions", $params))
    <h2 class="h4">Missions</h2>
    <table class="table table-sm mb-2">
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
    @if (array_key_exists("appels", $params))
    <h2 class="h4">Partenaires contactés</h2>
    <table class="table table-sm mb-2">
      <tr>
        <th>Date</th>
        <th>Nom</th>
        <th>Numéro</th>
        <th>Commentaire</th>
      </tr>
      @if (count($intervention->appels) == 0)
      <tr>
        <td>Aucun appel effectué.</td>
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
    @if (array_key_exists("groupes", $params))
    <h2 class="h4">Groupes alarmés</h2>
    <table class="table table-sm mb-2">
      @if (count($intervention->groupes) == 0)
      <tr>
        <td>Aucun groupe</td>
      </tr>
      @endif
      @foreach ($intervention->groupes as $groupe)
      <tr>
        <td>{{ $groupes[$groupe->groupe_id]->no }} {{ $groupes[$groupe->groupe_id]->designation }}</td>
      </tr>
      @endforeach
    </table>
    @endif
    {{-- Informations générales --}}
    <table class="table table-sm table-striped mt-2">
      <thead>
        <tr>
          <th>Nom prénom</th>
          <th class="text-center">Fonction</th>
          <th class="text-center">Présent</th>
          <th class="text-center">Remplacé</th>
          <th class="text-center">Excusé</th>
        </tr>
      </thead>
      {{-- <tbody>
        @foreach ($intervention->sapeurs as $presence)
        <tr>
          <td>{{ $presence->display }}</td>
          <td>{{ $presence->fonction_id ? $fonctions[$presence->fonction_id] : '' }}</td>
          <td>
            <div class="form-check text-center">
              <label class="form-check-label" for="present-{{$presence->sapeur_id}}"><input type="checkbox" class="form-check-input" id="present-{{$presence->sapeur_id}}" @if ($presence->present)
                checked="checked"
                @endif
                >&#8203;</label>
            </div>
          </td>
          <td>
            <div class="form-check text-center">
              <label class="form-check-label" for="remplace-{{$presence->sapeur_id}}"><input type="checkbox" class="form-check-input" id="remplace-{{$presence->sapeur_id}}" @if ($presence->remplace)
                checked="checked"
                @endif
                >&#8203;</label>
            </div>
          </td>
          <td>
            <div class="form-check text-center">
              <label class="form-check-label" for="excuse-{{$presence->sapeur_id}}" class="custom-control-label"><input type="checkbox" class="form-check-input" id="excuse-{{$presence->sapeur_id}}" @if ($presence->excuse_type_id)
                checked="checked"
                @endif
                ><span>{{ $presence->excuse_type_id ? $excuses[$presence->excuse_type_id] : '' }}</span></label>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody> --}}
      <thead>
        <tr>
          {{-- <th colspan="5">Nombre : {{ count($intervention->sapeurs) }}</th> --}}
        </tr>
      </thead>
    </table>
  </div>
</body>

</html>