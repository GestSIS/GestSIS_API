<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/assets/print.css">
  <title>Fiche sapeur</title>
</head>

<?php
if (!function_exists('formatDate')) {
    function formatDate($value)
    {
        return implode('.', array_reverse(explode('-', $value)));
    }
}
?>

<body>
  <div class="container-fluid">
    <h1>Fiche sapeur</h1>
    <div class="row mb-3">
      <div class="col-3">
        <label for="m-sap-nom">Civilité</label>
        <input type="text" value="{{ $sapeur->civilite->designation }}" class="form-control form-control-sm" />
      </div>
      <div class="col-4">
        <label for="m-sap-nom">Nom</label>
        <input id="m-sap-nom" value="{{ $sapeur->nom }}" type="text" class="form-control form-control-sm"
          name="nom" />
      </div>
      <div class="col-4">
        <label for="m-sap-prenom">Prénom</label>
        <input id="m-sap-prenom" value="{{ $sapeur->prenom }}" type="text" class="form-control form-control-sm"
          name="prenom" />
      </div>
      <div class="col-1">
        <label for="m-sap-suffixe">Suffixe</label>
        <font-awesome-icon
          v-tooltip.bottom="
          'Permet de différencier deux personnes ayant le même nom et prénom.'
        "
          class="ms-1" :icon="['far', 'question-circle']" />
        <input id="m-sap-suffixe" value="{{ $sapeur->suffixe }}" type="text" class="form-control form-control-sm"
          name="suffixe" />
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-3">
        <label for="m-sap-no-rue">Localité</label>
        <input type="localite" value="{{ $sapeur->localite->npa }} {{ $sapeur->localite->designation }}"
          class="form-control form-control-sm" />
      </div>
      <div class="col-6">
        <label for="m-sap-rue">Rue</label>
        <input id="m-sap-rue" value="{{ $sapeur->rue }}" type="text" class="form-control form-control-sm"
          name="rue" />
      </div>
      <div class="col-3">
        <label for="m-sap-no-rue">N°</label>
        <input id="m-sap-no-rue" value="{{ $sapeur->no_rue }}" type="text" class="form-control form-control-sm"
          name="no_rue" />
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-6">
        <label for="m-sap-avs">N° AVS</label>
        <input id="m-sap-avs" value="{{ $sapeur->no_avs }}" type="text" class="form-control form-control-sm"
          name="no_avs" />
      </div>
      <div class="col-6">
        <label for="m-sap-cotisation_avs">Cotisation AVS</label>
        <div class="form-check text-center col-6">
          <input id="m-sap-cotisation_avs" @checked($sapeur->cotisation_avs) type="checkbox" class="form-check-input" />
          <label class="form-check-label" for="m-sap-cotisation_avs"></label>
        </div>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-6">
        <label for="m-sap-email">Email</label>
        <div class="input-group input-group-sm">
          <input id="m-sap-email" value="{{ $sapeur->email }}" class="form-control form-control-sm" type="email"
            name="email" />
        </div>
      </div>
      <div class="col-6">
        <label for="m-sap-date-naissance">Date de naissance</label>
        <div class="input-group input-group-sm">
          <input id="m-sap-date-naissance" value="{{ $sapeur->date_naissance }}" class="form-control form-control-sm"
            type="date" name="date_naissance" />
        </div>
      </div>
    </div>
    <div class="mb-3">
      <label for="m-sap-remarques">Remarques</label>
      <textarea id="m-sap-remarques" value="{{ $sapeur->remarque }}" class="form-control form-control-sm" rows="3"
        name="remarques"></textarea>
    </div>

    <h2 class="h3">Téléphone</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      <tr>
        <th>Priorité</th>
        <th>Numéro</th>
        <th>Type</th>
        <th>Export RTA</th>
      </tr>
      @if (count($telephones) == 0)
        <tr>
          <td colspan="4">Aucun téléphone</td>
        </tr>
      @endif
      @foreach ($telephones as $t)
        <tr>
          <td>{{ $t->priorite }}</td>
          <td>{{ $t->numero }}</td>
          <td>{{ $t->telephoneType->type }}</td>
          <td><input @checked($t->rta) type="checkbox" class="form-check-input" /></td>
        </tr>
      @endforeach
    </table>

    <h2 class="h3">Références professionnelles</h2>
    <div class="row mb-3">
      <div class="col-4">
        <label for="m-sap-profession">Profession</label>
        <input id="m-sap-profession" value="{{ $sapeur->profession }}" type="text"
          class="form-control form-control-sm" name="profession" />
      </div>
      <div class="col-4">
        <label for="m-sap-employeur">Employeur</label>
        <input id="m-sap-employeur" value="{{ $sapeur->employeur }}" type="text"
          class="form-control form-control-sm" name="employeur" />
      </div>
      <div class="col-4">
        <label for="m-sap-lieu-travail">Lieu de travail</label>
        <input id="m-sap-lieu-travail" value="{{ $sapeur->lieu_de_travail }}" type="text"
          class="form-control form-control-sm" name="lieu_travail" />
      </div>
    </div>

    <h2 class="h3">Grade actuel et fonction principale</h2>
    <div class="row mb-3">
      <div class="col-6">
        <label>Fonction principale</label>
        <input type="text" value="{{ $sapeur->fonction?->nom }}" class="form-control form-control-sm" />
      </div>
      <div class="col-6">
        <label>Grade actuel</label>
        <input type="text" value="{{ $sapeur->grade?->designation }}" class="form-control form-control-sm" />
      </div>
    </div>

    <h2 class="h3">Cours</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      <tr>
        <th>Date</th>
        <th>Cours</th>
        <th>Lieu</th>
        <th>Durée [jours]</th>
      </tr>
      @if (count($cours) == 0)
        <tr>
          <td colspan="4">Aucun cours suivi</td>
        </tr>
      @endif
      @foreach ($cours as $c)
        <tr>
          <td>{{ formatDate($c->date) }}</td>
          <td>{{ $c->cours->designation }}</td>
          <td>{{ $c->localite->designation }}</td>
          <td>{{ $c->duree }}</td>
        </tr>
      @endforeach
    </table>

    <h2 class="h3">Fonctions</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      <tr>
        <th>Début</th>
        <th>Fin</th>
        <th>Fonction</th>
        <th>Remarque</th>
      </tr>
      @if (count($fonctions) == 0)
        <tr>
          <td colspan="4">Aucune fonction</td>
        </tr>
      @endif
      @foreach ($fonctions as $f)
        <tr>
          <td>{{ formatDate($f->debut) }}</td>
          <td>{{ formatDate($f->fin) }}</td>
          <td>{{ $f->fonction->nom }}</td>
          <td>{{ $f->remarque }}</td>
        </tr>
      @endforeach
    </table>

    <h2 class="h3">Grade</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      <tr>
        <th>Date</th>
        <th>Grade</th>
        <th>Remarque</th>
      </tr>
      @if (count($grades) == 0)
        <tr>
          <td colspan="3">Aucun grade</td>
        </tr>
      @endif
      @foreach ($grades as $g)
        <tr>
          <td>{{ formatDate($g->date) }}</td>
          <td>{{ $g->grade->designation }}</td>
          <td>{{ $g->remarque }}</td>
        </tr>
      @endforeach
    </table>

    <h2 class="h3">Mutations</h2>
    <table class="table table-sm table-bordered table-striped mb-2">
      <tr>
        <th>Incorporation</th>
        <th>Sortie</th>
        <th>Motif</th>
        <th>Localité</th>
      </tr>
      @if (count($mutations) == 0)
        <tr>
          <td colspan="3">Aucun grade</td>
        </tr>
      @endif
      @foreach ($mutations as $m)
        <tr>
          <td>{{ formatDate($m->incorporation) }}</td>
          <td>{{ formatDate($m->sortie) }}</td>
          <td>{{ $m->motif }}</td>
          <td>{{ $m->localite->designation }}</td>
        </tr>
      @endforeach
    </table>

    {{-- TODO: Materiels --}}
  </div>
</body>

</html>
