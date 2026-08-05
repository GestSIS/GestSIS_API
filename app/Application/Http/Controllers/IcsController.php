<?php

namespace App\Application\Http\Controllers;

use App\Models\Exercice;
use App\Models\IcsToken;
use App\Models\ExerciceSapeur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Symfony\Component\HttpFoundation\Response;

class IcsController extends Controller
{
    /**
     * Flux ICS public (protégé par token) des exercices d'un sapeur pour un SIS donné.
     * Accessible sans authentification JWT : consommé par des clients calendrier (Google, Outlook, Apple).
     */
    public function show(Request $request, string $sisKey, string $token)
    {
        if (!App::environment('testing')) {
            if (!in_array($sisKey, config('database.dbs'), true)) {
                abort(404);
            }

            Config::set('database.default', 'db_' . $sisKey);
        }

        $icsToken = IcsToken::where('token', $token)->first();
        if ($icsToken === null) {
            abort(404);
        }

        $exerciceIds = ExerciceSapeur::where('sapeur_id', $icsToken->sapeur_id)->pluck('exercice_id');

        $exercices = Exercice::whereIn('id', $exerciceIds)
            ->orderBy('date')
            ->get();

        $calendar = Calendar::create('Agenda GestSIS')
            ->refreshInterval(60)
            ->event($exercices->map(function (Exercice $exercice) {
                $starts = Carbon::parse($exercice->date . ' ' . $exercice->heure);
                $ends = $starts->clone()->addMinutes((int) $exercice->duree);

                $event = Event::create($exercice->designation)
                    ->uniqueIdentifier("exercice-{$exercice->id}@gestsis")
                    ->startsAt($starts)
                    ->endsAt($ends);

                if ($exercice->communications !== null && $exercice->communications !== '') {
                    $event->description($exercice->communications);
                }

                if ($exercice->lieu !== null && $exercice->lieu !== '') {
                    $event->address($exercice->lieu);
                }

                return $event;
            })->all());

        return response($calendar->get(), Response::HTTP_OK, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="agenda.ics"',
        ]);
    }
}
