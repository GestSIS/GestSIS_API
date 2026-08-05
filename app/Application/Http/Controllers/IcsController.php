<?php

namespace App\Application\Http\Controllers;

use App\Models\Exercice;
use App\Models\IcsToken;
use App\Models\ExerciceSapeur;
use App\Models\SisParam;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\EventStatus;
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

        $convoqueParExerciceId = ExerciceSapeur::where('sapeur_id', $icsToken->sapeur_id)
            ->pluck('convoque', 'exercice_id');

        $exercices = Exercice::whereIn('id', $convoqueParExerciceId->keys())
            ->orderBy('date')
            ->get();

        $sisNom = SisParam::first()?->nom;

        $calendar = Calendar::create($sisNom !== null ? "GestSIS - {$sisNom}" : 'GestSIS')
            ->refreshInterval(60)
            ->event($exercices->map(function (Exercice $exercice) use ($convoqueParExerciceId) {
                $starts = Carbon::parse($exercice->date . ' ' . $exercice->heure, 'Europe/Zurich');
                $ends = $starts->clone()->addMinutes((int) $exercice->duree);
                $convoque = (bool) $convoqueParExerciceId->get($exercice->id);
                $annule = $exercice->statut === 0;

                $suffixe = match (true) {
                    $annule => ' - ANNULÉ',
                    !$convoque => ' - pour info',
                    default => '',
                };

                $event = Event::create($exercice->designation . $suffixe)
                    ->uniqueIdentifier("exercice-{$exercice->id}@gestsis")
                    ->startsAt($starts)
                    ->endsAt($ends);

                if ($annule) {
                    $event->status(EventStatus::Cancelled)->transparent();
                } elseif (!$convoque) {
                    $event->status(EventStatus::Tentative)->transparent();
                }

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
