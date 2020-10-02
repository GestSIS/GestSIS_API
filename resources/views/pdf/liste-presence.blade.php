<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" crossorigin="anonymous">
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

  <title>Décomptes sapeurs</title>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-8"><h1>Présences</h1></div>
      <div class="col-4"><p>{{$exercice->localite_id}} : {{$exercice->lieu}}</p></div>
    </div>
    <div class="row">
      <div class="col-2">{{$exercice->date}}</div>
      <div class="col-2">{{$exercice->heure}}</div>
      <div class="col-8">{{$exercice->designation}}</div>
    </div>
    <div class="row">
      <div class="col-12">{{$exercice->communications}}</div>
    </div>
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Nom</th>
          <th class="text-center">Convoque</th>
          <th class="text-center">Present</th>
          <th class="text-center">Remplace</th>
          <th class="text-center">Excuse</th>
          <th class="text-center">Amende</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($exercice->sapeurs as $presence)
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
                ><span>Excuse non valable</span></label>
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
    </table>
  </div>
</body>

</html>