<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" crossorigin="anonymous">
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

    <title>Convocation</title>
</head>
<body>
    <?php
    use Carbon\Carbon;
    setlocale(LC_TIME, 'fr_CH');
    Carbon::setLocale('fr');
    // Some helper functions
    function formatDate($value)
    {
        return Carbon::parse($value)->translatedFormat('D j.m.y');
    }

    function formatHeure($value)
    {
        return substr($value, 0, 5);
    }
    ?>
    <div class=" ">
        @foreach($sapeurs as $sapeur)
        <h1>{{ $params['titre'] }}</h1>
        <div class="mt-3" style="margin-left:50%">
            <div class="col-6">
                {{-- {{dd($sapeur, $civilites)}} --}}
                <p class="m-0">{{ $civilites[$sapeur['civilite_id']] }}</p>
                <p class="m-0">{{ $sapeur['nom'] }} {{ $sapeur['prenom'] }}</p>
                <p class="m-0">{{ $sapeur['rue'] }} {{ $sapeur['no_rue'] }}</p>
                <p class="m-0">{{ $localites[$sapeur['localite_id']] }}</p>
            </div>
        </div>
        <div class="mt-3">
            <p>
                @foreach(explode("\n",str_replace(["\r\n","\n\r","\r"],"\n",$params['texteDebut'])) as $line)
                {{ $line }}<br/>
                @endforeach
            </p>
        </div>
        <table class="table table-sm table-striped">
            <tbody>
            <?php
                foreach ($sapeur['exercices'] as $convocation) {
                    // dd($convocation['exercice_id'], $exercices);
                    $exercice = $exercices[$convocation['exercice_id']];
                ?>
                <tr>
                    <td>{{ formatDate($exercice['date']) }}</td>
                    <td>{{ formatHeure($exercice['heure']) }}</td>
                    @switch($params['format'])
                    @case(1)
                    <td>{{ $localites[$exercice['localite_id']] }} : {{ $exercice['lieu'] }}<br />{{ $categories[$exercice['exercice_categorie_id']] }} : {{ $exercice['communications'] }}</td>
                    @break;
                    @case(2)
                    <td>{{ $localites[$exercice['localite_id']] }} : {{ $exercice['lieu'] }} - {{ $categories[$exercice['exercice_categorie_id']] }}<br />{{ $exercice['communications'] }}</td>
                    @break;
                    @case(3)
                    <td>{{ $localites[$exercice['localite_id']] }} : {{ $exercice['lieu'] }} - {{ $categories[$exercice['exercice_categorie_id']] }} : {{ $exercice['communications'] }}</td>
                    @break;
                    @endswitch
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        <div>
            <p>
                @foreach(explode("\n",str_replace(["\r\n","\n\r","\r"],"\n",$params['texteFin'])) as $line)
                {{$line}}<br />
                @endforeach
            </p>
        </div>
        @if(!$loop->last)
        <div class="page-break"></div>
        @endif
        @endforeach
    </div>
</body>

</html>