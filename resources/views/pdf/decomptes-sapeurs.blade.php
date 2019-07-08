<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style>
        .page-break {
            page-break-after: always;
        }
    </style>

    <title>Décomptes sapeurs</title>
</head>
<body>
<div class="container">
    @foreach($sapeurs as $sapeur)
        <sapeur>
            <exercices>
                <exercice>
                    <ecritures>
                        <ecriture></ecriture>
                        <ecriture></ecriture>
                        <ecriture></ecriture>
                    </ecritures>
                </exercice>
            </exercices>
        </sapeur>
        <div class="row">Some test content</div>
        <div class="page-break"></div>
    @endforeach
</div>
</body>
</html>
