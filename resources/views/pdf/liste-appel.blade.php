<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/assets/print.css">
  <title>Liste d'appel</title>
</head>

<body>
  <div class="container-fluid">
    <h1>Liste d'appel</h1>
    <table class="table table-sm mb-2">
      <tr>
        <td><strong>Date</strong> : {{ str_replace('-', '.', $exercice->date) }}</td>
        <td><strong>Lieu</strong> : {{ $exercice->localite->designation }}, {{ $exercice->lieu }}</td>
      </tr>
      <tr>
        <td><strong>Heure</strong> : {{ substr($exercice->heure, 0, 5) }}</td>
        <td><strong>Désignation</strong> : {{ $exercice->designation }}</td>
      </tr>
      <tr>
        <td colspan="2"><strong>Communications</strong> : {{ $exercice->communications }}</td>
      </tr>
    </table>
    <table class="table table-sm table-striped mt-2">
      <thead>
        <tr>
          <th>Nom prénom</th>
          <th>Fonction</th>
          <th class="text-center">Présent</th>
          <th class="text-center">Absent</th>
          <th class="text-center">Remplacé</th>
          <th class="text-center">Excusé</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($exercice->sapeurs as $presence)
          <tr>
            <td>{{ $presence->display }}</td>
            <td>{{ $presence->fonction_id ? $fonctions[$presence->fonction_id] : '' }}</td>
            <td>
              <div class="form-check text-center">
                <label class="form-check-label" for="present-{{ $presence->sapeur_id }}"><input type="checkbox"
                    class="form-check-input" id="present-{{ $presence->sapeur_id }}"
                    @if ($presence->present) checked="checked" @endif>&#8203;</label>
              </div>
            </td>
            <td>
              <div class="form-check text-center">
                <label class="form-check-label" for="absent-{{ $presence->sapeur_id }}"><input type="checkbox"
                    class="form-check-input" id="absent-{{ $presence->sapeur_id }}"
                    @if ($presence->absent) checked="checked" @endif>&#8203;</label>
              </div>
            </td>
            <td>
              <div class="form-check text-center">
                <label class="form-check-label" for="remplace-{{ $presence->sapeur_id }}"><input type="checkbox"
                    class="form-check-input" id="remplace-{{ $presence->sapeur_id }}"
                    @if ($presence->remplace) checked="checked" @endif>&#8203;</label>
              </div>
            </td>
            <td>
              <div class="form-check text-center">
                <label class="form-check-label" for="excuse-{{ $presence->sapeur_id }}"
                  class="custom-control-label"><input type="checkbox" class="form-check-input"
                    id="excuse-{{ $presence->sapeur_id }}"
                    @if ($presence->excuse_type_id) checked="checked" @endif><span>{{ $presence->excuse_type_id ? $excuses[$presence->excuse_type_id] : '' }}</span></label>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
      <thead>
        <tr>
          <th colspan="6">Nombre : {{ count($exercice->sapeurs) }}</th>
        </tr>
      </thead>
    </table>
  </div>
</body>

</html>
