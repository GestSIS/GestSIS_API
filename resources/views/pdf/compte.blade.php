<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css"
    crossorigin="anonymous">
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
    <h1>Justificatif comptable</h1>
    @component('components/single-compte', ["compte"=>$compte, "sapeurs"=>$sapeurs])
    @endcomponent
  </div>
</body>
</html>
