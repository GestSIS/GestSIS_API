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

  <title>Liste de présence</title>
</head>
<body>
  <div class="container-fluid">
    <h1>Présences</h1>
    <table class="table table-sm mb-2">
      <tr>
        <td><strong>Date</strong> : {{ str_replace('-', '.', $exercice->date) }}</td>
        <td><strong>Lieu</strong> : {{ $exercice->localite->designation }}, {{ $exercice->lieu }}</td>
      </tr>
      <tr>
        <td><strong>Heure</strong> : {{ substr($exercice->heure, 0, 5) }}</td>
        <td><strong>Désignation</strong> : {{ $exercice->designation }}</td>
      </tr>
    </table>
    <table class="table table-sm table-striped mt-2">
      <thead>
        <tr>
          <th>Nom</th>
          <th class="text-center">Convoqué</th>
          <th class="text-center">Present</th>
          <th class="text-center">Remplacé</th>
          <th class="text-center">Excusé</th>
          <th class="text-center">Amende</th>
        </tr>
      </thead>
      <tbody>
        <?
        $nb = 0;
        $convoque = 0;
        $present = 0;
        $excuse = 0;
        $remplace = 0;
        $amende = 0;
        ?>
        @foreach ($exercice->sapeurs as $presence)
        <?
        $nb++;
        if ($presence->present) $present++;
        if ($presence->convoque) $convoque++;
        if ($presence->remplace) $remplace++;
        if ($presence->excuse_type_id) $excuse++;
        ?>
        <tr>
          <td>{{ $presence->display }}</td>
          <td>
            <div class="form-check text-center">
              <label class="form-check-label" for="convoque-{{$presence->sapeur_id}}"><input type="checkbox" class="form-check-input" id="convoque-{{$presence->sapeur_id}}" @if ($presence->convoque)
                checked="checked"
                @endif
                >&#8203;</label>
            </div>
          </td>
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
          <td>
            <div class="form-check text-center">
              <label class="form-check-label" for="amende-{{$presence->sapeur_id}}"><input type="checkbox" class="form-check-input" id="amende-{{$presence->sapeur_id}}" @if ($presence->amende)
                checked="checked"
                @endif
                >&#8203;</label>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
      <thead>
        <tr>
          <th>Nombre : {{ count($exercice->sapeurs) }}</th>
          <th class="text-center">{{ $convoque }}</th>
          <th class="text-center">{{ $present }}</th>
          <th class="text-center">{{ $remplace }}</th>
          <th class="text-center">{{ $excuse }}</th>
          <th class="text-center">{{ $amende }}</th>
        </tr>
      </thead>
    </table>
  </div>
</body>

</html>