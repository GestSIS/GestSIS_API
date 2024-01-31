<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="/assets/print.css">
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
  <div class="container-fluid">
    <h1>Trombinoscope</h1>
    <div class="d-grid gap-2" style="grid-template-columns: repeat(6, minmax(0, 1fr));">
      @foreach ($sapeurs as $sapeur)
        <div class="d-flex flex-column align-items-center">
          <img class="img-fluid" name="{{ $sapeur->id }}"
            src="{{ $sapeurService->getPhotoSapeurAsHtmlEncoding($sapeur->id, $sisId) ?? $imageDefault }}"
            alt="{{ $sapeur->nom }} {{ $sapeur->prenom }}" />
          <label for="{{ $sapeur->id }}">{{ $sapeur->nom }} {{ $sapeur->prenom }}</label>
        </div>
      @endforeach
    </div>
  </div>
</body>

</html>
